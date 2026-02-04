<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\SenderIds;
use App\Models\SenderId;
use App\Models\SmsProvider;
use App\Models\User;
use App\Models\Tenant;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Mockery;

class SenderIdStatusCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_check_status_and_see_details()
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['role' => 'super_admin']); // Ensure super_admin logic works if applicable, role names vary
        // The policy/component says: if (!auth()->user()->isSuperAdmin()) abort(403);
        // Assuming isSuperAdmin checks a role or permission. I'll mock typical admin setup.
        // If User factory doesn't handle roles well, we might need to adjust.
        // Let's assume 'super_admin' role exists or I can set is_super_admin flag if it exists.
        // Looking at common setups, I'll try setting the property or method return.
        
        // Actually, let's look at User model if needed, but standard is usually checking a role column.
        
        $senderId = SenderId::create([
            'user_id' => $admin->id,
            'sender_id' => 'TestSender',
            'purpose' => 'Testing',
            'status' => 'pending'
        ]);

        // Mock SmsService
        $mockService = Mockery::mock(SmsService::class);
        $mockService->shouldReceive('checkSenderIdStatus')
            ->with('TestSender')
            ->once()
            ->andReturn([
                'status' => 'success',
                'remote_status' => 'Approved',
                'mapped_status' => 'payment_pending',
                'raw' => [
                    'status' => 'success',
                    'code' => '2000',
                    'summary' => [
                        'sender_name' => 'TestSender',
                        'status' => 'Approved'
                    ]
                ]
            ]);

        $this->actingAs($admin);

        Livewire::test(SenderIds::class)
            ->call('checkStatus', $senderId->id, $mockService)
            ->assertSet('showStatusModal', true)
            ->assertSet('statusCheckResult.mapped_status', 'payment_pending')
            ->assertSee('Approved') // Remote status
            ->assertSee('2000'); // Raw code

        // Assert DB update happened (auto-update logic)
        $this->assertEquals('payment_pending', $senderId->fresh()->status);
    }
}
