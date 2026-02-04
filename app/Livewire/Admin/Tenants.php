<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Devrabiul\ToastMagic\Facades\ToastMagic;

#[Layout('layouts.admin')]
class Tenants extends Component
{
    use WithPagination;

    // Search & Sort
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $filterStatus = '';

    // Modals & Actions
    public $confirmingSuspension = false;
    public $confirmingReactivation = false;
    public $confirmingDeletion = false;
    public $showingCreditModal = false;
    
    public $targetTenantId = null;
    public $targetTenantName = '';

    // Credits Modal Form
    public $creditAmount = '';
    public $creditReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // --- Actions Setup ---

    public function confirmSuspend($id, $name)
    {
        $this->targetTenantId = $id;
        $this->targetTenantName = $name;
        $this->confirmingSuspension = true;
    }

    public function confirmReactivate($id, $name)
    {
        $this->targetTenantId = $id;
        $this->targetTenantName = $name;
        $this->confirmingReactivation = true;
    }

    public function confirmDelete($id, $name)
    {
        $this->targetTenantId = $id;
        $this->targetTenantName = $name;
        $this->confirmingDeletion = true;
    }

    public function openCreditModal($id, $name)
    {
        $this->targetTenantId = $id;
        $this->targetTenantName = $name;
        $this->creditAmount = '';
        $this->creditReason = '';
        $this->showingCreditModal = true;
    }

    // --- Actions Execution ---

    public function suspend()
    {
        $tenant = Tenant::findOrFail($this->targetTenantId);
        $tenant->update(['status' => 'suspended']);
        
        $this->confirmingSuspension = false;
        ToastMagic::success("Tenant {$tenant->name} has been suspended.");
    }

    public function reactivate()
    {
        $tenant = Tenant::findOrFail($this->targetTenantId);
        $tenant->update(['status' => 'active']);
        
        $this->confirmingReactivation = false;
        ToastMagic::success("Tenant {$tenant->name} has been reactivated.");
    }

    public function delete()
    {
        $tenant = Tenant::findOrFail($this->targetTenantId);
        $tenant->delete();

        $this->confirmingDeletion = false;
        ToastMagic::success("Tenant {$tenant->name} has been deleted.");
    }

    public function adjustCredits()
    {
        $this->validate([
            'creditAmount' => 'required|integer',
            'creditReason' => 'required|string|max:255',
        ]);

        $tenant = Tenant::findOrFail($this->targetTenantId);
        $amount = (int) $this->creditAmount;

        $owner = User::where('tenant_id', $tenant->id)->where('is_account_owner', true)->first();
        if (!$owner) {
             ToastMagic::error('Tenant has no account owner to assign transaction to.');
             return;
        }

        DB::transaction(function () use ($tenant, $owner, $amount) {
            if ($amount > 0) {
                $tenant->increment('sms_credits', $amount);
            } else {
                $tenant->decrement('sms_credits', abs($amount));
            }

            Transaction::create([
                'user_id' => $owner->id,
                'type' => 'adjustment',
                'amount' => $amount,
                'description' => "Admin Adjustment: " . $this->creditReason,
                'reference' => 'ADM-' . Str::upper(Str::random(8)),
                'balance_after' => $tenant->fresh()->sms_credits,
            ]);
        });

        $this->showingCreditModal = false;
        ToastMagic::success("Credits adjusted successfully.");
    }

    public function render()
    {
        $query = Tenant::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $tenants = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.tenants', [
            'tenants' => $tenants
        ]);
    }
}
