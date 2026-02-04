<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\Contact;
use App\Models\SenderId;
use App\Models\Message;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SendGroupSmsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create(['id' => 1, 'sms_credits' => 1000]);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        SenderId::factory()->create([
            'user_id' => $this->user->id,
            'sender_id' => 'ALERTS',
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function test_it_can_send_to_a_group_with_personalization()
    {
        $group = Group::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by_user_id' => $this->user->id,
            'name' => 'Clients'
        ]);

        Contact::create([
            'tenant_id' => $this->tenant->id,
            'created_by_user_id' => $this->user->id,
            'group_id' => $group->id,
            'name' => 'John Doe',
            'phone' => '+233241234567'
        ]);

        Contact::create([
            'tenant_id' => $this->tenant->id,
            'created_by_user_id' => $this->user->id,
            'group_id' => $group->id,
            'name' => 'Jane Smith',
            'phone' => '+233247654321'
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Messaging\SendSms::class)
            ->set('targetType', 'group')
            ->set('selectedGroupId', $group->id)
            ->set('sender_id', 'ALERTS')
            ->set('message', 'Hello {{first_name}}, welcome!')
            ->call('sendSms')
            ->assertHasNoErrors();

        $this->assertEquals(2, Message::count());
        $this->assertDatabaseHas('messages', [
            'recipient' => '+233241234567',
            'body' => 'Hello John, welcome!'
        ]);
        $this->assertDatabaseHas('messages', [
            'recipient' => '+233247654321',
            'body' => 'Hello Jane, welcome!'
        ]);
    }

    /** @test */
    public function test_it_validates_insufficient_credits_for_groups()
    {
        $this->tenant->update(['sms_credits' => 1]);

        $group = Group::factory()->create(['tenant_id' => $this->tenant->id]);
        Contact::factory()->count(5)->create([
            'tenant_id' => $this->tenant->id,
            'group_id' => $group->id
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Messaging\SendSms::class)
            ->set('targetType', 'group')
            ->set('selectedGroupId', $group->id)
            ->set('sender_id', 'ALERTS')
            ->set('message', 'A long message that takes parts.')
            ->call('sendSms');

        $this->assertEquals(0, Message::count());
        $this->assertEquals(1, $this->tenant->fresh()->sms_credits);
    }
}
