<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::latest()->paginate(20);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function impersonate(Tenant $tenant)
    {
        // Find the owner of the tenant
        $user = User::where('tenant_id', $tenant->id)->where('is_account_owner', true)->first();

        if ($user) {
            Auth::login($user);
            return redirect()->route('dashboard')->with('status', "You are now impersonating {$tenant->name}'s admin.");
        }

        return back()->with('error', 'No owner found for this tenant.');
    }
    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);
        return back()->with('status', "Tenant {$tenant->name} has been suspended.");
    }

    public function reactivate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);
        return back()->with('status', "Tenant {$tenant->name} has been reactivated.");
    }

    public function adjustCredits(Request $request, Tenant $tenant)
    {
        $request->validate([
            'amount' => 'required|integer', // Can be negative
            'reason' => 'required|string|max:255',
        ]);

        $amount = (int) $request->amount;
        
        // Find tenant owner for the transaction record
        $owner = User::where('tenant_id', $tenant->id)->where('is_account_owner', true)->first();
        if (!$owner) {
             return back()->with('error', 'Tenant has no account owner to assign transaction to.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($tenant, $owner, $amount, $request) {
            if ($amount > 0) {
                $tenant->increment('sms_credits', $amount);
            } else {
                $tenant->decrement('sms_credits', abs($amount));
            }

            \App\Models\Transaction::create([
                'user_id' => $owner->id,
                'type' => 'adjustment',
                'amount' => $amount,
                'description' => "Admin Adjustment: " . $request->reason,
                'reference' => 'ADM-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8)),
                'balance_after' => $tenant->fresh()->sms_credits,
            ]);
        });

        return back()->with('status', "Credits adjusted successfully.");
    }
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return back()->with('status', "Tenant {$tenant->name} has been deleted.");
    }
}
