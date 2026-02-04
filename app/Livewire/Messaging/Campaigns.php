<?php

namespace App\Livewire\Messaging;

use App\Models\Campaign;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Campaigns extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $campaign = Campaign::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        if ($campaign->status === 'sending') {
            $this->dispatch('toastMagic', status: 'error', message: 'Cannot delete a campaign that is currently sending.');
            return;
        }

        // Delete associated messages logic (if cascading wasn't set in DB)
        // For safety, we can delete messages first.
        $campaign->messages()->delete();
        $campaign->delete();

        $this->dispatch('toastMagic', status: 'success', message: 'Campaign deleted successfully.');
    }

    public function cancel($id)
    {
        $campaign = Campaign::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        if ($campaign->status !== 'scheduled') {
            $this->dispatch('toastMagic', status: 'error', message: 'Only scheduled campaigns can be cancelled.');
            return;
        }

        $campaign->update(['status' => 'cancelled']);
        
        // Update associated scheduled messages to cancelled
        $campaign->messages()
            ->where('status', 'scheduled')
            ->update(['status' => 'cancelled']);

        $this->dispatch('toastMagic', status: 'success', message: 'Campaign cancelled successfully.');
    }

    public function syncStatus($id)
    {
        $campaign = Campaign::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        
        /** @var \App\Services\SmsService $smsService */
        $smsService = app(\App\Services\SmsService::class);
        
        $result = $smsService->syncCampaignStatus($campaign);

        if (($result['status'] ?? '') === 'success') {
            $count = $result['count'] ?? 0;
            $this->dispatch('toastMagic', status: 'success', message: "Synced status for {$count} messages.");
        } else {
             $this->dispatch('toastMagic', status: 'error', message: $result['message'] ?? 'Failed to sync status.');
        }
    }

    public function render()
    {
        $campaigns = Campaign::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->withCount([
                'messages',
                'messages as delivered_count' => function ($query) {
                    $query->where('status', 'delivered');
                },
                'messages as failed_count' => function ($query) {
                    $query->where('status', 'failed');
                }
            ])
            ->latest()
            ->paginate(10);

        return view('livewire.messaging.campaigns', [
            'campaigns' => $campaigns
        ]);
    }
}
