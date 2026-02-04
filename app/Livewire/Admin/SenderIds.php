<?php

namespace App\Livewire\Admin;

use App\Models\SenderId;
use App\Services\SmsService;
use Livewire\Component;
use Livewire\WithPagination;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Gate;

class SenderIds extends Component
{
    use WithPagination;

    public $rejectionReason = '';
    public $selectedSenderId = null;

    public function mount()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }
    }

    public function approve($id)
    {
        $senderId = SenderId::findOrFail($id);
        $senderId->approve();

        ToastMagic::success(__('Sender ID :sender approved.', ['sender' => $senderId->sender_id]));
    }

    public function reject($id)
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:3|max:255',
        ]);

        $senderId = SenderId::findOrFail($id);
        $senderId->reject($this->rejectionReason);

        $this->rejectionReason = '';
        $this->dispatch('close-modal', 'reject-sender-id');

        ToastMagic::success(__('Sender ID :sender rejected.', ['sender' => $senderId->sender_id]));
    }

    public $statusCheckResult = null;
    public $showStatusModal = false;

    public function checkStatus($id, SmsService $smsService)
    {
        $senderId = SenderId::findOrFail($id);
        $result = $smsService->checkSenderIdStatus($senderId->sender_id);

        $this->statusCheckResult = $result;
        $this->showStatusModal = true;

        if ($result['status'] === 'success') {
            $mappedStatus = $result['mapped_status'] ?? 'pending';
            // Auto-update if different
             if ($senderId->status !== $mappedStatus) {
                // Respect Admin Rejection
                if ($senderId->status !== 'rejected') {
                     $senderId->update(['status' => $mappedStatus]);
                }
            }
        }
    }

    public function refreshStatus($id, SmsService $smsService)
    {
        $senderId = SenderId::findOrFail($id);
        $result = $smsService->checkSenderIdStatus($senderId->sender_id);

        if ($result['status'] === 'success') {
            $mappedStatus = $result['mapped_status'] ?? 'pending';
            
            if ($senderId->status !== $mappedStatus) {
                 // Respect Admin Rejection
                if ($senderId->status === 'rejected') {
                    ToastMagic::info("Sender ID is locally rejected. Status from provider is: " . ucfirst($mappedStatus)); 
                    return;
                }

                $senderId->update(['status' => $mappedStatus]);
                ToastMagic::success("Status updated to: " . ucfirst($mappedStatus)); 
            } else {
                 ToastMagic::info("Status is still: " . ucfirst($mappedStatus));
            }
        } else {
            ToastMagic::error($result['message'] ?? 'Failed to check status.');
        }
    }

    public function delete($id)
    {
        $senderId = SenderId::findOrFail($id);
        $senderId->delete();

        ToastMagic::success(__('Sender ID deleted successfully.'));
    }

    public function toggleStatus($id)
    {
        $senderId = SenderId::findOrFail($id);
        
        // If it's active, disable it (reject it without reason implies disabled by admin)
        // Or we can toggle between 'active' and 'rejected' (or a new 'disabled' status if validation supports it)
        // Based on SendSms loop: where('status', 'active')
        // So anything not 'active' is disabled.
        
        if ($senderId->status === 'active') {
            $senderId->update([
                'status' => 'rejected',
                'reason' => 'Disabled by Administrator'
            ]);
            ToastMagic::success(__('Sender ID disabled.'));
        } elseif ($senderId->status === 'rejected' || $senderId->status === 'disabled') {
            // Allow re-enabling? Or should they go through approval?
            // Let's assume re-enabling to 'active' for now if it was previously active.
            // But safety first: maybe set to 'approved' (payment_pending) or just 'active'?
            // If it was manually disabled, we can manually enable.
            $senderId->update(['status' => 'active']);
            ToastMagic::success(__('Sender ID activated.'));
        }
    }

    public $showDetailsModal = false;
    public $viewingSenderId = null;

    public function viewDetails($id)
    {
        $this->viewingSenderId = SenderId::with('user.tenant')->find($id);
        $this->showDetailsModal = true;
    }

    public function render()
    {
        return view('livewire.admin.sender-ids', [
            'senderIds' => SenderId::with('user.tenant')->latest()->paginate(10),
        ]);
    }
}
