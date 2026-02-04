<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_page_displays_current_balance(): void
    {
        $tenant = Tenant::factory()->create(['sms_credits' => 1000]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/billing');

        $response->assertStatus(200);
        $response->assertSee('1,000'); // Formatted balance
    }

    public function test_user_can_initiate_top_up(): void
    {
        $tenant = Tenant::factory()->create(['sms_credits' => 500]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $package = \App\Models\CreditPackage::create([
            'name' => 'Test Package',
            'credits' => 100,
            'price' => 10,
            'unit_price' => 0.10,
            'is_active' => true
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Billing::class)
            ->set('selectedPackageId', $package->id)
            ->set('gateway', 'stripe')
            ->call('topUp')
            ->assertRedirect(route('billing.checkout', ['package_id' => $package->id, 'gateway' => 'stripe']));
            
        // Note: Credits are not updated until PaymentController processes the payment
    }

    public function test_top_up_validates_package_selection(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Billing::class)
            ->set('selectedPackageId', 999999) // Non-existent ID
            ->call('topUp')
            ->assertHasErrors(['selectedPackageId']);
    }

    public function test_transaction_history_displays_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 500,
            'description' => 'Test top-up',
            'reference' => 'TEST-123',
            'balance_after' => 500,
        ]);

        $response = $this->actingAs($user)->get('/billing');

        $response->assertSee('Test top-up');
        $response->assertSee('TEST-123');
    }
}
