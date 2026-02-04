<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class Billing extends Component
{
    use WithPagination;

    public $selectedPackageId = null;
    public $gateway = 'stripe'; // Default gateway
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Auto-select the first active package if available
        $firstPackage = \App\Models\CreditPackage::where('is_active', true)->first();
        if ($firstPackage) {
            $this->selectedPackageId = $firstPackage->id;
        }
    }

    public function topUp()
    {
        $this->validate([
            'selectedPackageId' => 'required|exists:credit_packages,id',
            'gateway' => 'required|in:stripe,paystack,flutterwave',
        ]);

        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            ToastMagic::error('Your account is not associated with a billing team. Please contact support.');
            return;
        }

        if ($this->gateway === 'paystack') {
            $this->dispatch('init-paystack-payment', 
                packageId: $this->selectedPackageId, 
                gateway: $this->gateway
            );
            return;
        }

        // Production: Redirect to Payment Controller with Package ID
        return redirect()->route('billing.checkout', ['package_id' => $this->selectedPackageId, 'gateway' => $this->gateway]);
    }

    public function render()
    {
        return view('livewire.billing', [
            'packages' => \App\Models\CreditPackage::where('is_active', true)->get(),
            'transactions' => Transaction::where('user_id', Auth::id())
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('reference', 'like', '%' . $this->search . '%')
                          ->orWhere('description', 'like', '%' . $this->search . '%');
                    });
                })
                ->latest()
                ->paginate(10),
        ]);
    }
}
