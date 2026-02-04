<?php

namespace App\Livewire\Messaging;

use App\Models\SenderId;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class SenderIds extends Component
{
    public $senderId = '';
    public $purpose = '';
    
    // Payment Modal State
    public $showPaymentModal = false;
    public $paymentSenderId = null;
    public $selectedGateway = 'paystack'; // Default

    // Details Modal State
    public $showDetailsModal = false;
    public $viewingSenderId = null;

    public function viewDetails($id)
    {
        $this->viewingSenderId = Auth::user()->senderIds()->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function requestSenderId(SmsService $smsService)
    {
        $this->validate([
            'senderId' => ['required', 'string', 'max:11', 'min:3', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'purpose' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        // Register with API provider
        $result = $smsService->registerSenderId($this->senderId, $this->purpose);

        if ($result['status'] === 'error') {
            ToastMagic::error($result['message']);
            return;
        }

        // Create local record
        Auth::user()->senderIds()->create([
            'sender_id' => $this->senderId,
            'purpose' => $this->purpose,
            'status' => 'pending',
        ]);

        $this->reset(['senderId', 'purpose']);
        $this->dispatch('close-modal', 'request-sender-id');

        ToastMagic::success($result['message'] ?? __('Sender ID requested successfully.'));
    }

    public function initiatePayment($senderId)
    {
        $this->paymentSenderId = $senderId;
        $this->showPaymentModal = true;
    }

    public function processPayment()
    {
        $this->validate([
            'selectedGateway' => 'required|in:stripe,paystack,flutterwave',
        ]);

        if ($this->selectedGateway === 'paystack') {
            $this->dispatch('init-sender-id-payment', 
                senderId: $this->paymentSenderId, 
                gateway: $this->selectedGateway
            );
            return;
        }

        return redirect()->route('billing.checkout', [
            'type' => 'sender_id', 
            'id' => $this->paymentSenderId, 
            'gateway' => $this->selectedGateway
        ]);
    }

    public function refreshStatus($id, SmsService $smsService)
    {
        $senderId = Auth::user()->senderIds()->find($id);

        if (!$senderId) {
            ToastMagic::error('Sender ID not found.');
            return;
        }

        if ($senderId->status === 'rejected') {
            ToastMagic::error('Sender ID is rejected. Please contact support.');
            return;
        }

        $result = $smsService->checkSenderIdStatus($senderId->sender_id);

        if ($result['status'] === 'success') {
            $mappedStatus = $result['mapped_status'] ?? 'pending';
            
            // Only update if status changed
            if ($senderId->status !== $mappedStatus) {
                $senderId->update(['status' => $mappedStatus]);
                
                // If rejected, maybe store reason if API provides it? 
                // Currently API result structure in SmsService doesn't explicitly return reason in mapping block.
                
                ToastMagic::success("Status updated to: " . ucfirst($mappedStatus));
            } else {
                 ToastMagic::info("Status is still: " . ucfirst($mappedStatus));
            }
        } else {
            ToastMagic::error($result['message'] ?? 'Failed to check status.');
        }
    }

    public function render()
    {
        return view('livewire.messaging.sender-ids', [
            'senderIds' => Auth::user()->senderIds()->latest()->get(),
        ]);
    }
}
