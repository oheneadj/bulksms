<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Messaging\SenderIds;
use App\Models\SenderId;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SenderIdRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_update_status_if_locally_rejected()
    {
        $user = User::factory()->create();
        $senderId = SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'RejectedID',
            'status' => 'rejected',
            'purpose' => 'Testing'
        ]);

        $this->actingAs($user);

        // Mock Service to return 'active' (to simulate discrepancy)
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldNotReceive('checkSenderIdStatus'); // Should NOT be called
        });

        Livewire::test(SenderIds::class)
            ->call('refreshStatus', $senderId->id);

        $this->assertEquals('rejected', $senderId->fresh()->status);
    }

    public function test_it_updates_status_if_not_rejected()
    {
        $user = User::factory()->create();
        $senderId = SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'PendingID',
            'status' => 'pending',
            'purpose' => 'Testing'
        ]);

        $this->actingAs($user);

        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('checkSenderIdStatus')
                ->with('PendingID')
                ->once()
                ->andReturn(['status' => 'success', 'mapped_status' => 'active']);
        });

        Livewire::test(SenderIds::class)
            ->call('refreshStatus', $senderId->id);

        $this->assertEquals('active', $senderId->fresh()->status);
    }
}
