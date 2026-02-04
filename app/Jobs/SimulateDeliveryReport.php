<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SimulateDeliveryReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messageId;

    /**
     * Create a new job instance.
     */
    public function __construct($messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var \App\Models\Message|null $message */
        $message = Message::find($this->messageId, ['id', 'status']);

        if (!$message || $message->status !== 'sent') {
            return;
        }

        // Simulate 90% delivery rate
        $isDelivered = rand(1, 100) <= 90;
        $status = $isDelivered ? 'delivered' : 'failed';

        $message->update(['status' => $status]);

        Log::info("Simulated delivery report for message {$this->messageId}: {$status}");

        // Dispatch Webhook Event
        $tenantId = \App\Models\Message::where('id', $this->messageId)->value('user_id'); // Assuming user_id maps to tenant context logic or direct retrieval
        // Actually, we need to load the relationship to be sure.
        $messageModel = Message::with('user.tenant')->find($this->messageId);
        
        if ($messageModel && $messageModel->user && $messageModel->user->tenant) {
            $webhooks = \App\Models\Webhook::where('tenant_id', $messageModel->user->tenant->id)
                ->where('is_active', true)
                ->get();
            
            foreach ($webhooks as $webhook) {
                // Check if subscribed to this event
                if (in_array("message.{$status}", $webhook->events ?? [])) {
                    \App\Jobs\WebhookJob::dispatch($webhook, "message.{$status}", [
                        'message_id' => $messageModel->id,
                        'recipient' => $messageModel->recipient,
                        'status' => $status,
                        'timestamp' => now()->toIso8601String(),
                    ]);
                }
            }
        }
    }
}
