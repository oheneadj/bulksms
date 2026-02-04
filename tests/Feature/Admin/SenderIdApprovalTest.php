<?php

namespace Tests\Feature\Admin;

use App\Models\SenderId;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SenderIdApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'super_admin'
        ]);

        $this->user = User::factory()->create([
            'role' => 'user'
        ]);
    }

    /** @test */
    public function test_only_super_admins_can_access_admin_sender_ids_page()
    {
        $this->actingAs($this->user)
            ->get(route('admin.sender-ids'))
            ->assertStatus(403);

        $this->actingAs($this->admin)
            ->get(route('admin.sender-ids'))
            ->assertStatus(200);
    }

    /** @test */
    public function test_super_admin_can_approve_a_sender_id()
    {
        $senderId = SenderId::create([
            'user_id' => $this->user->id,
            'sender_id' => 'TESTING',
            'status' => 'pending',
            'purpose' => 'Test registration'
        ]);

        $this->actingAs($this->admin);

        Livewire::test('admin.sender-ids')
            ->call('approve', $senderId->id)
            ->assertHasNoErrors();

        $this->assertEquals('approved', $senderId->fresh()->status);
    }

    /** @test */
    public function test_super_admin_can_reject_a_sender_id_with_a_reason()
    {
        $senderId = SenderId::create([
            'user_id' => $this->user->id,
            'sender_id' => 'REJECTME',
            'status' => 'pending',
            'purpose' => 'Test registration'
        ]);

        $this->actingAs($this->admin);

        Livewire::test('admin.sender-ids')
            ->set('rejectionReason', 'Not a valid sender name')
            ->call('reject', $senderId->id)
            ->assertHasNoErrors();

        $this->assertEquals('rejected', $senderId->fresh()->status);
        $this->assertEquals('Not a valid sender name', $senderId->fresh()->reason);
    }
}
