<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Tenants;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Livewire\Livewire;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $tenant;
    protected $owner;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::factory()->create([
            'status' => 'active',
            'sms_credits' => 100,
            'name' => 'Target Company'
        ]);

        $this->owner = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_account_owner' => true,
        ]);

        $this->superAdmin = User::factory()->create();
        
        \Illuminate\Support\Facades\Gate::define('super-admin', function ($user) {
            return true;
        });
    }

    #[Test]
    public function page_renders_with_tenants()
    {
        $this->actingAs($this->superAdmin);
        
        Livewire::test(Tenants::class)
            ->assertStatus(200)
            ->assertSee($this->tenant->name);
    }

    #[Test]
    public function search_filters_tenants()
    {
        $otherTenant = Tenant::factory()->create(['name' => 'Other Company']);
        
        $this->actingAs($this->superAdmin);

        Livewire::test(Tenants::class)
            ->set('search', 'Target')
            ->assertSee('Target Company')
            ->assertDontSee('Other Company');
    }

    #[Test]
    public function super_admin_can_suspend_tenant()
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(Tenants::class)
            ->call('confirmSuspend', $this->tenant->id, $this->tenant->name)
            ->assertSet('confirmingSuspension', true)
            ->call('suspend');

        $this->assertEquals('suspended', $this->tenant->fresh()->status);
    }

    #[Test]
    public function super_admin_can_reactivate_tenant()
    {
        $this->tenant->update(['status' => 'suspended']);
        $this->actingAs($this->superAdmin);

        Livewire::test(Tenants::class)
            ->call('confirmReactivate', $this->tenant->id, $this->tenant->name)
            ->assertSet('confirmingReactivation', true)
            ->call('reactivate');

        $this->assertEquals('active', $this->tenant->fresh()->status);
    }

    #[Test]
    public function super_admin_can_adjust_credits()
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(Tenants::class)
            ->call('openCreditModal', $this->tenant->id, $this->tenant->name)
            ->assertSet('showingCreditModal', true)
            ->set('creditAmount', 50)
            ->set('creditReason', 'Bonus')
            ->call('adjustCredits');

        $this->assertEquals(150, $this->tenant->fresh()->sms_credits);
        
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->owner->id,
            'type' => 'adjustment',
            'amount' => 50,
            'description' => 'Admin Adjustment: Bonus',
        ]);
    }

    #[Test]
    public function super_admin_can_soft_delete_tenant()
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(Tenants::class)
            ->call('confirmDelete', $this->tenant->id, $this->tenant->name)
            ->assertSet('confirmingDeletion', true)
            ->call('delete');

        $this->assertSoftDeleted('tenants', ['id' => $this->tenant->id]);
    }
}
