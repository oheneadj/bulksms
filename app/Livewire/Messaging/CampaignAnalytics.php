<?php

namespace App\Livewire\Messaging;

use App\Models\Campaign;
use App\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CampaignAnalytics extends Component
{
    use WithPagination;

    public Campaign $campaign;
    public $search = '';
    public $filterStatus = '';

    public function mount(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) {
            abort(403);
        }
        $this->campaign = $campaign;
    }

    public function exportCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="campaign_' . $this->campaign->id . '_report.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Recipient', 'Status', 'Sent At', 'Cost', 'Gateway ID']);

            $this->campaign->messages()->chunk(1000, function ($messages) use ($file) {
                foreach ($messages as $message) {
                    fputcsv($file, [
                        $message->recipient,
                        $message->status,
                        $message->sent_at ? $message->sent_at->toDateTimeString() : '',
                        $message->cost,
                        $message->gateway_message_id
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function syncStatus()
    {
        /** @var \App\Services\SmsService $smsService */
        $smsService = app(\App\Services\SmsService::class);
        
        $result = $smsService->syncCampaignStatus($this->campaign);

        if (($result['status'] ?? '') === 'success') {
            $count = $result['count'] ?? 0;
            $this->dispatch('toastMagic', status: 'success', message: "Synced status for {$count} messages.");
        } else {
             $this->dispatch('toastMagic', status: 'error', message: $result['message'] ?? 'Failed to sync status.');
        }
    }

    public function render()
    {
        // Calculate stats
        $stats = [
            'total' => $this->campaign->messages()->count(),
            'delivered' => $this->campaign->messages()->where('status', 'delivered')->count(),
            'failed' => $this->campaign->messages()->where('status', 'failed')->count(),
            'pending' => $this->campaign->messages()->whereIn('status', ['queued', 'scheduled', 'pending', 'sent'])->count(),
            'cost' => $this->campaign->messages()->sum('cost'),
        ];
        
        $stats['delivery_rate'] = $stats['total'] > 0 ? round(($stats['delivered'] / $stats['total']) * 100, 1) : 0;

        $messages = $this->campaign->messages()
            ->when($this->search, function ($q) {
                $q->where('recipient', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.messaging.campaign-analytics', [
            'stats' => $stats,
            'messages' => $messages
        ]);
    }
}
