<?php

namespace App\Livewire\Messaging;

use App\Models\Campaign;
use App\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CampaignDetails extends Component
{
    use WithPagination;

    public $campaignId;
    public $search = '';
    public $statusFilter = '';

    public function mount($id)
    {
        $this->campaignId = $id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $campaign = Campaign::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->campaignId);

        $query = Message::where('campaign_id', $this->campaignId);

        if ($this->search) {
            $query->where('recipient', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', '=', $this->statusFilter);
        }

        $messages = $query->latest()->paginate(20);

        $messages = $query->latest()->paginate(20);

        // Stats
        $total = Message::where('campaign_id', $this->campaignId)->count();
        $delivered = Message::where('campaign_id', $this->campaignId)->where('status', 'delivered')->count();
        $failed = Message::where('campaign_id', $this->campaignId)->where('status', 'failed')->count();
        $queued = Message::where('campaign_id', $this->campaignId)->whereIn('status', ['queued', 'sent'])->count();
        $scheduled = Message::where('campaign_id', $this->campaignId)->where('status', 'scheduled')->count();

        $stats = [
            'total' => $total,
            'delivered' => $delivered,
            'failed' => $failed,
            'pending' => $queued + $scheduled,
            'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100, 1) : 0,
            'failure_rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0,
        ];

        // Chart Data
        $chartData = [
            'series' => [$delivered, $failed, $queued, $scheduled],
            'labels' => ['Delivered', 'Failed', 'Queued', 'Scheduled'],
            'colors' => ['#10b981', '#f43f5e', '#f59e0b', '#6366f1']
        ];

        return view('livewire.messaging.campaign-details', [
            'campaign' => $campaign,
            'messages' => $messages,
            'stats' => $stats,
            'chartData' => $chartData
        ]);
    }
}
