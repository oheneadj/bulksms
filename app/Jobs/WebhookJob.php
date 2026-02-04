<?php

namespace App\Jobs;

use App\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhookId;
    protected $payload;
    protected $event;

    /**
     * Create a new job instance.
     */
    public function __construct(Webhook $webhook, $event, $payload)
    {
        $this->webhookId = $webhook->id;
        $this->event = $event;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var \App\Models\Webhook|null $webhook */
        $webhook = Webhook::find($this->webhookId);

        if (!$webhook || !$webhook->is_active) {
            return;
        }

        try {
            // Add ID and timestamp to payload if not present
            $data = array_merge([
                'id' => 'evt_' . uniqid(),
                'event' => $this->event,
                'created_at' => now()->toIso8601String(),
            ], $this->payload);

            $signature = hash_hmac('sha256', json_encode($data), $webhook->secret);

            Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-BulkSMS-Signature' => $signature,
                    'X-BulkSMS-Event' => $this->event,
                ])
                ->post($webhook->url, $data);
                
        } catch (\Exception $e) {
            Log::error("Webhook failed for ID {$this->webhookId}: " . $e->getMessage());
            $this->release(60); // Retry in 60s
        }
    }
}
