<?php

namespace App\Livewire\Developer;

use App\Models\Webhook;
use Illuminate\Support\Str;
use Livewire\Component;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Http;

class Webhooks extends Component
{
    public $url = '';
    public $events = ['message.delivered', 'message.failed']; // Default selection
    public $selectedEvents = [];
    
    public $showCreateModal = false;
    public $editingWebhookId = null;

    public $secretToCopy = null;

    public $simulateWebhooks = false;

    protected $rules = [
        'url' => 'required|url',
        'selectedEvents' => 'required|array|min:1',
    ];

    public function mount()
    {
        $this->selectedEvents = $this->events;
        $this->simulateWebhooks = auth()->user()->tenant->simulate_webhooks ?? false;
    }

    public function updatedSimulateWebhooks($value)
    {
        auth()->user()->tenant->update(['simulate_webhooks' => $value]);
        ToastMagic::success($value ? 'Simulation mode enabled.' : 'Simulation mode disabled.');
    }

    public function createWebhook()
    {
        $this->validate();

        $secret = 'whsec_' . Str::random(32);

        Webhook::create([
            'tenant_id' => auth()->user()->tenant_id,
            'url' => $this->url,
            'secret' => $secret,
            'events' => $this->selectedEvents,
            'is_active' => true,
        ]);

        $this->reset(['url', 'showCreateModal']);
        $this->secretToCopy = $secret; // Show secret once
        ToastMagic::success('Webhook created successfully.');
    }

    public function deleteWebhook($id)
    {
        Webhook::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id)->delete();
        ToastMagic::success('Webhook deleted.');
    }

    public function toggleStatus($id)
    {
        $webhook = Webhook::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $webhook->update(['is_active' => !$webhook->is_active]);
        ToastMagic::success('Webhook status updated.');
    }

    public function testWebhook($id)
    {
        $webhook = Webhook::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        
        try {
            // Send a ping event
            $payload = [
                'id' => 'evt_' . Str::random(24),
                'event' => 'ping',
                'created_at' => now()->toIso8601String(),
                'data' => [
                    'message' => 'This is a test event from BulkSMS.',
                ],
            ];

            $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-BulkSMS-Signature' => $signature,
                'X-BulkSMS-Event' => 'ping',
            ])->post($webhook->url, $payload);

            if ($response->successful()) {
                ToastMagic::success('Test event sent successfully (HTTP ' . $response->status() . ').');
            } else {
                ToastMagic::error('Webhook failed with HTTP ' . $response->status());
            }

        } catch (\Exception $e) {
            ToastMagic::error('Could not connect to webhook URL.');
        }
    }

    public function render()
    {
        return view('livewire.developer.webhooks', [
            'webhooks' => Webhook::where('tenant_id', auth()->user()->tenant_id)->get(),
        ]);
    }
}
