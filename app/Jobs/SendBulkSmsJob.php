<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use App\Models\Campaign;

class SendBulkSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipients;
    protected $messageBody;
    protected $senderId;
    protected $campaignId; // Local DB Campaign ID
    protected $messageIds; // Array of local Message IDs to update

    /**
     * Create a new job instance.
     *
     * @param array $recipients Array of phone numbers
     * @param string $messageBody The raw message
     * @param string $senderId
     * @param int|null $campaignId Local Campaign ID
     * @param array $messageIds Local Message IDs belonging to this batch
     */
    public function __construct(array $recipients, string $messageBody, string $senderId, ?int $campaignId, array $messageIds)
    {
        $this->recipients = $recipients;
        $this->messageBody = $messageBody;
        $this->senderId = $senderId;
        $this->campaignId = $campaignId;
        $this->messageIds = $messageIds;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        $count = count($this->recipients);
        if ($count === 0) return;

        // 1. Calculate Cost (Restored)
        $parts = $smsService->calculateParts($this->messageBody);
        $totalCost = $count * $parts; 

        // 2. Identify Tenant
        $tenant = null;
        $userId = null;
        
        if ($this->campaignId) {
            $campaign = Campaign::with('user.tenant')->find($this->campaignId);
            $tenant = $campaign?->user?->tenant;
            $userId = $campaign?->user_id;
        } elseif (!empty($this->messageIds)) {
            $message = Message::with('user.tenant')->find($this->messageIds[0]);
            $tenant = $message?->user?->tenant;
            $userId = $message?->user_id;
        }

        if (!$tenant) {
            // Should not happen, but safe fallback
            $this->failJob("Tenant not found for billing.");
            return;
        }

        // 3. Sender ID Validation (Active Check)
        $senderIdString = $this->senderId; 
        $senderIdRecord = \App\Models\SenderId::where('sender_id', $senderIdString)
            ->where('user_id', $userId) 
            ->first();

        if (!$senderIdRecord || $senderIdRecord->status !== 'active') {
             $this->failJob("Sender ID '{$senderIdString}' is not active. Status: " . ($senderIdRecord->status ?? 'NotFound'));
             return;
        }

        // 4. Deduction Logic
        try {
            if (!$tenant->deductCredits($totalCost)) {
                $this->failJob("Insufficient credits. Required: {$totalCost}, Available: {$tenant->sms_credits}");
                return;
            }

            // 4. Log Transaction
            \App\Models\Transaction::create([
                'user_id' => $userId, // Log under the user who initiated
                'type' => 'usage',
                'amount' => $totalCost,
                'description' => "Bulk SMS Campaign" . ($this->campaignId ? " #{$this->campaignId}" : ""),
                'reference' => 'SMS-' . uniqid(),
                'balance_after' => $tenant->sms_credits,
            ]);

            // Update Campaign cost
            if ($this->campaignId && isset($campaign)) {
                $campaign->increment('total_cost', $totalCost);
            }

        } catch (\Exception $e) {
            $this->failJob("Billing Error: " . $e->getMessage());
            return;
        }

        // 5. Send via Bulk API (Quick SMS)
        $result = $smsService->send(
            $this->recipients,
            $this->messageBody,
            null, // No single message ID to update initially
            null, // Schedule
            $this->senderId // Pass the validated sender ID
        );

        if (($result['status'] ?? 'error') === 'success') {
            $gatewayCampaignId = $result['sid'] ?? null; // This is the _id from mNotify

            // 6. Update all local messages with success and the shared gateway ID
            Message::whereIn('id', $this->messageIds)->update([
                'status' => 'sent',
                'sent_at' => now(),
                'gateway_message_id' => $gatewayCampaignId, 
                'cost' => $parts, // Store cost per message
            ]);

            // Update Campaign if exists
            if ($this->campaignId && $gatewayCampaignId) {
                Campaign::where('id', $this->campaignId)->update(['status' => 'sending']);
            }
        } else {
            // Failed at Gateway - Refund?
            // For now, let's mark as failed. Refunds could be a future feature.
            // Ideally we should refund here if the API call failed completely.
            $tenant->increment('sms_credits', $totalCost); // Refund
            
            \App\Models\Transaction::create([
                'user_id' => $userId,
                'type' => 'refund',
                'amount' => $totalCost,
                'description' => "Refund: Failed Bulk Submission",
                'reference' => 'RFD-' . uniqid(),
                'balance_after' => $tenant->sms_credits,
            ]);

            $this->failJob("Gateway Error: " . ($result['message'] ?? 'Unknown'));
        }
    }

    protected function failJob($reason)
    {
        Message::whereIn('id', $this->messageIds)->update([
            'status' => 'failed',
            'gateway_message_id' => 'system-failed'
        ]);
        
        if ($this->campaignId) {
            Campaign::where('id', $this->campaignId)->update(['status' => 'failed']);
        }
        
        // Log the failure?
        \Illuminate\Support\Facades\Log::error("BulkSendJob Failed: $reason");
    }
}
