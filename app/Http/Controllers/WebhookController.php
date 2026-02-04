<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Twilio Delivery Status Callback
     */
    public function handleTwilio(Request $request)
    {
        $messageSid = $request->input('MessageSid');
        $status = $request->input('MessageStatus');

        Log::info("Twilio Webhook: SID={$messageSid}, Status={$status}");

        if ($messageSid && $status) {
            $this->updateMessageStatusByGatewayId($messageSid, $status);
        }

        return response()->xml('<Response></Response>');
    }

    /**
     * Handle mNotify Delivery Status Callback
     * Note: mNotify structure varies, assuming standard POST based on docs/common patterns
     */
    public function handleMnotify(Request $request)
    {
        // mNotify usually sends JSON or POST params
        // recipient, status, summary_id (message id from their system)
        
        $gatewayId = $request->input('id') ?? $request->input('summary_id');
        $status = $request->input('status');

        Log::info("mNotify Webhook: ID={$gatewayId}, Status={$status}");

        if ($gatewayId && $status) {
            $this->updateMessageStatusByGatewayId($gatewayId, $status);
        }

        return response()->json(['status' => 'success']);
    }

    protected function updateMessageStatusByGatewayId($gatewayId, $providerStatus)
    {
        $message = Message::where('gateway_message_id', $gatewayId)->first();

        if ($message) {
            // Map provider status to local status
            $status = match (strtolower($providerStatus)) {
                'delivered', 'successful' => 'delivered',
                'undelivered', 'failed', 'rejected' => 'failed',
                'sent' => 'sent',
                default => 'sent', // Keep as sent if unknown intermediate state
            };

            if ($message->status !== $status) {
                $message->update(['status' => $status, 'delivered_at' => $status === 'delivered' ? now() : null]);
                
                // Trigger outbound webhooks for the user
                $this->triggerUserWebhooks($message, $status);
            }
        }
    }

    protected function triggerUserWebhooks($message, $status)
    {
        // Re-use logic from SimulateDeliveryReport or extract to a shared service/job
        // For now, I'll dispatch the same job logic if I can refactor, 
        // or just dispatch the WebhookJob directly if I have the webhook config.
        
        $tenantId = $message->user->tenant_id ?? null; // Assuming relation
        if (!$tenantId) return;

        $webhooks = \App\Models\Webhook::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();
        
        foreach ($webhooks as $webhook) {
            if (in_array("message.{$status}", $webhook->events ?? [])) {
                \App\Jobs\WebhookJob::dispatch($webhook, "message.{$status}", [
                    'message_id' => $message->id,
                    'recipient' => $message->recipient,
                    'status' => $status,
                    'timestamp' => now()->toIso8601String(),
                ]);
            }
        }
    }
}
