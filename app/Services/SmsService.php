<?php

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $provider;

    public function __construct()
    {
        $this->provider = config('services.sms_provider', 'twilio');
    }

    /**
     * Send an SMS message using the configured provider.
     */
    public function send($to, $body, $messageId = null, $schedule = null, $senderId = null)
    {
        $provider = $this->getProvider($to);
        
        if (!$provider) {
            Log::error("No active SMS provider found for recipient: {$to}");
            $this->updateMessageStatus($messageId, 'failed');
            return ['status' => 'error', 'message' => 'No active SMS provider configured for this region.'];
        }

        if ($provider->provider === 'mnotify') {
            return $this->sendMnotify($to, $body, $messageId, $provider, $schedule, $senderId);
        }

        return $this->sendTwilio($to, $body, $messageId, $provider);
    }

    protected function getProvider($recipient = null)
    {
        // Smart Routing Logic
        if ($recipient) {
            $checkNumber = is_array($recipient) ? ($recipient[0] ?? '') : $recipient;

            // Clean number
            $cleanNumber = preg_replace('/[^0-9]/', '', $checkNumber);
            
            // Check for Ghana (+233)
            if (str_starts_with($cleanNumber, '233')) {
                $localProvider = \App\Models\SmsProvider::where('is_active', true)
                    ->whereIn('provider', ['mnotify', 'hubtel'])
                    ->orderBy('priority', 'desc')
                    ->first();
                
                if ($localProvider) {
                    return $localProvider;
                }
            }
        }

        // Default / Fallback (International)
        return \App\Models\SmsProvider::where('is_active', true)
            ->where('provider', 'mnotify') // Default to mNotify for all regions
            ->orderBy('priority', 'desc')
            ->first() 
            ?? \App\Models\SmsProvider::where('is_active', true)->orderBy('priority', 'desc')->first(); // Ultimate fallback
    }

    protected function sendTwilio($to, $body, $messageId, $provider)
    {
        $config = $provider->config;
        $sid = $config['sid'] ?? null;
        $token = $config['token'] ?? null;
        $from = $config['from'] ?? null;

        if (!$sid || !$token) {
            Log::error("Twilio credentials missing in provider config.");
            return ['status' => 'error', 'message' => 'Twilio not configured properly.'];
        }

        try {
            $client = new TwilioClient($sid, $token);
            $payload = [
                'from' => $from,
                'body' => $body,
                'statusCallback' => route('webhooks.sms.twilio'),
            ];

            Log::info("Sending Twilio SMS", ['to' => $to, 'params' => $payload]);

            $response = $client->messages->create($to, $payload);

            $this->updateMessageStatus($messageId, 'sent', $response->sid);

            return ['status' => 'success', 'sid' => $response->sid];
        } catch (\Exception $e) {
            Log::error("Twilio Error: " . $e->getMessage());
            $this->updateMessageStatus($messageId, 'failed');
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function sendMnotify($to, $body, $messageId, $provider, $schedule = null, $senderId = null)
    {
        $config = $provider->config;
        $key = $config['key'] ?? null;
        $defaultSender = $config['sender_id'] ?? null;
        $sender = $senderId ?: $defaultSender;

        if (!$key) {
            Log::error("Mnotify API Key missing in provider config.");
            return ['status' => 'error', 'message' => 'Mnotify key not configured.'];
        }

        // Handle Recipients: Ensure it's an array of strings
        // If $to is a single string (comma separated or single number), split/wrap it.
        $recipients = is_array($to) ? $to : explode(',', $to);
        $recipients = array_map('trim', $recipients);

        $payload = [
            'recipient' => $recipients,
            'sender' => $sender,
            'message' => $body,
            'is_schedule' => false,
        ];

        // Handle Scheduling
        if ($schedule) {
            // $schedule should be 'YYYY-MM-DD HH:mm'
            // If it's a Carbon instance, format it.
            $date = $schedule instanceof \Carbon\Carbon ? $schedule->format('Y-m-d H:i') : $schedule;
            
            $payload['is_schedule'] = true;
            $payload['schedule_date'] = $date;
        }

        try {
            // Log the request (redacting key)
            Log::info("Sending mNotify SMS", [
                'endpoint' => "https://api.mnotify.com/api/sms/quick", 
                'payload' => $payload
            ]);

            $response = Http::post("https://api.mnotify.com/api/sms/quick?key={$key}", $payload);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? '') === 'success') {
                $summary = $data['summary'] ?? [];
                
                // _id is the campaign id according to docs
                // The sample response shows "_id": "..." 
                $gatewayId = $summary['_id'] ?? $summary['id'] ?? null; 
                
                $this->updateMessageStatus($messageId, 'sent', $gatewayId);
                return ['status' => 'success', 'sid' => $gatewayId];
            }

            Log::error("Mnotify Error: " . ($data['message'] ?? $data['error'] ?? 'Unknown error'), ['payload' => $payload, 'response' => $data]);
            
            $errorMessage = strtolower($data['message'] ?? $data['error'] ?? '');
            if (str_contains($errorMessage, 'sender id') && (str_contains($errorMessage, 'not registered') || str_contains($errorMessage, 'not approved'))) {
                 // Auto-reject this Sender ID globally as it's invalid on the gateway
                 if ($sender) {
                     \App\Models\SenderId::where('sender_id', $sender)
                        ->where('status', '!=', 'rejected')
                        ->update([
                            'status' => 'rejected',
                            'reason' => 'Automatically rejected by gateway: ' . ($data['message'] ?? $data['error'] ?? 'Not registered on gateway.')
                        ]);
                 }
            }

            $this->updateMessageStatus($messageId, 'failed');
            return ['status' => 'error', 'message' => $data['message'] ?? $data['error'] ?? 'Mnotify delivery failed'];
            
        } catch (\Exception $e) {
            Log::error("Mnotify Exception: " . $e->getMessage());
            $this->updateMessageStatus($messageId, 'failed');
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function registerSenderId($senderName, $purpose)
    {
        // Explicitly look for mNotify provider for registration
        $provider = \App\Models\SmsProvider::where('provider', 'mnotify')->where('is_active', true)->first();
        
        if ($provider) {
             return $this->registerMnotifySenderId($senderName, $purpose, $provider);
        }

        // Fallback for providers that don't support/require API registration
        return ['status' => 'success', 'message' => 'Sender ID logged. Review required.'];
    }

    protected function registerMnotifySenderId($senderName, $purpose, $provider)
    {
        $key = $provider->config['key'] ?? null;

        if (!$key) {
            return ['status' => 'error', 'message' => 'Mnotify API Key missing.'];
        }

        try {
            $response = Http::post("https://api.mnotify.com/api/senderid/register?key={$key}", [
                'sender_name' => $senderName,
                'purpose' => $purpose,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? '') === 'success') {
                return ['status' => 'success', 'message' => $data['message'] ?? 'Sender ID registration submitted.'];
            }

            return ['status' => 'error', 'message' => $data['message'] ?? 'Mnotify registration failed.'];
        } catch (\Exception $e) {
            Log::error("Mnotify Register Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function checkSenderIdStatus($senderName)
    {
         $provider = \App\Models\SmsProvider::where('provider', 'mnotify')->where('is_active', true)->first();
         
         if (!$provider) return ['status' => 'error', 'message' => 'Provider not configured'];
         
         $key = $provider->config['key'] ?? null;
         if (!$key) return ['status' => 'error', 'message' => 'API Key missing'];

         try {
            $response = Http::post("https://api.mnotify.com/api/senderid/status?key={$key}", [
                'sender_name' => $senderName,
            ]);

            $data = $response->json();
            
            if ($response->successful() && ($data['status'] ?? '') === 'success') {
                $status = $data['summary']['status'] ?? 'Pending';
                // Map mNotify status to our internal system
                $mappedStatus = match (strtolower($status)) {
                    'approved' => 'payment_pending', // Approved by mNotify means ready for payment in our system
                    'rejected' => 'rejected',
                    'pending' => 'pending',
                    default => 'pending'
                };
                
                return ['status' => 'success', 'remote_status' => $status, 'mapped_status' => $mappedStatus, 'raw' => $data];
            }
            
            return ['status' => 'error', 'message' => $data['message'] ?? 'Unknown error'];

         } catch (\Exception $e) {
            Log::error("Mnotify Status Check Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
         }
    }

    public function getCampaignDeliveryReport($campaignId, $status = null)
    {
        $provider = \App\Models\SmsProvider::where('provider', 'mnotify')->where('is_active', true)->first();
         
        if (!$provider) return ['status' => 'error', 'message' => 'Provider not configured'];
         
        $key = $provider->config['key'] ?? null;
        if (!$key) return ['status' => 'error', 'message' => 'API Key missing'];

        try {
            // EndPoint: https://api.mnotify.com/api/campaign/<id>/<status>
            // Default status is null (returns all)
            $url = "https://api.mnotify.com/api/campaign/{$campaignId}";
            if ($status) {
                $url .= "/{$status}";
            }
            
            $response = Http::get("{$url}?key={$key}");
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error("mNotify Campaign Report Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getSpecificSmsDeliveryReport($id)
    {
        $provider = \App\Models\SmsProvider::where('provider', 'mnotify')->where('is_active', true)->first();
         
        if (!$provider) return ['status' => 'error', 'message' => 'Provider not configured'];
         
        $key = $provider->config['key'] ?? null;
        if (!$key) return ['status' => 'error', 'message' => 'API Key missing'];

        try {
            // EndPoint: https://api.mnotify.com/api/status/<id>
            $response = Http::get("https://api.mnotify.com/api/status/{$id}?key={$key}");
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error("mNotify Specific Report Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getMnotifyBalance()
    {
        $provider = \App\Models\SmsProvider::where('provider', 'mnotify')->where('is_active', true)->first();
        if (!$provider) return null;

        $key = $provider->config['key'] ?? null;
        if (!$key) return null;

        try {
            $response = Http::get("https://api.mnotify.com/api/balance/sms?key={$key}");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("mNotify Balance Error: " . $e->getMessage());
        }
        return null;
    }

    public function getProviderBalances()
    {
        $balances = [];
        $providers = \App\Models\SmsProvider::where('is_active', true)->get();

        foreach ($providers as $provider) {
            if ($provider->provider === 'mnotify') {
                $data = $this->getMnotifyBalance();
                if ($data && ($data['status'] ?? '') === 'success') {
                    $balances['mnotify'] = [
                        'name' => 'mNotify',
                        'balance' => $data['balance'] ?? 0,
                        'bonus' => $data['bonus'] ?? 0,
                        'currency' => 'Credits' // mNotify uses credits usually
                    ];
                }
            }
            // Add other providers here if implemented (e.g. Twilio)
            // Twilio requires looking up Account resource
        }

        return $balances;
    }

    /**
     * Update message record status and gateway ID.
     */
    protected function updateMessageStatus($messageId, $status, $gatewayId = null)
    {
        if (!$messageId) return;

        $message = Message::find($messageId, ['id', 'status', 'sent_at', 'gateway_message_id']);
        if ($message instanceof Message) {
            $updateData = ['status' => $status];
            
            if ($status === 'sent') {
                $updateData['sent_at'] = now();
                if ($gatewayId) {
                    $updateData['gateway_message_id'] = $gatewayId;
                }
                
                // Check if simulation is enabled for the tenant
                $tenant = $message->user?->tenant;
                if ($tenant && $tenant->simulate_webhooks) {
                    // Simulate delivery report after 5-15 seconds
                    \App\Jobs\SimulateDeliveryReport::dispatch($messageId)->delay(now()->addSeconds(rand(5, 15)));
                }
            }

            $message->fill($updateData)->save();
        }
    }

    /**
     * Calculate message parts based on length.
     */
    public function calculateParts($body)
    {
        $length = mb_strlen($body);
        
        // Standard SMS is 160 chars. If multi-part, it's 153 chars per part (due to headers).
        if ($length <= 160) {
            return 1;
        }

        return ceil($length / 153);
    }

    /**
     * Sync campaign delivery status from provider
     */
    public function syncCampaignStatus(\App\Models\Campaign $campaign)
    {
        // We need the Gateway Campaign ID (mNotify ID) which is stored on the messages
        $firstMessage = $campaign->messages()->whereNotNull('gateway_message_id')->first();
        
        if (!$firstMessage || !$firstMessage->gateway_message_id) {
            return ['status' => 'error', 'message' => 'No external Campaign ID found for this campaign.'];
        }

        $gatewayId = $firstMessage->gateway_message_id;
        $response = $this->getCampaignDeliveryReport($gatewayId);

        if (($response['status'] ?? '') === 'success') {
            $report = $response['report'] ?? [];
            $updatedCount = 0;

            foreach ($report as $item) {
                // Map mNotify status to local status
                $remoteStatus = strtoupper($item['status'] ?? '');
                $localStatus = match ($remoteStatus) {
                    'DELIVERED' => 'delivered',
                    'UNDELIVERED', 'FAILED', 'REJECTED' => 'failed',
                    'SUBMITTED', 'SENT' => 'sent',
                    default => 'sent'
                };

                $recipient = $item['recipient'] ?? '';
                
                // Try to find the message by recipient within this campaign
                // Only update if status changed
                $affected = $campaign->messages()
                    ->where('recipient', $recipient) 
                    ->orWhere('recipient', '+' . $recipient)
                    ->update([
                        'status' => $localStatus,
                        'delivered_at' => ($localStatus === 'delivered') ? ($item['date_sent'] ?? now()) : null
                    ]);

                if ($affected) {
                    $updatedCount++;
                }
            }

            // Update Campaign Status based on aggregate
            $total = $campaign->messages()->count();
            $delivered = $campaign->messages()->where('status', 'delivered')->count();
            $failed = $campaign->messages()->where('status', 'failed')->count();

            if ($delivered + $failed >= $total && $total > 0) {
                 $campaign->update(['status' => 'completed']);
            }

            return ['status' => 'success', 'count' => $updatedCount];
        }

        return ['status' => 'error', 'message' => 'Failed to fetch report: ' . ($response['message'] ?? 'Unknown error')];
    }
}
