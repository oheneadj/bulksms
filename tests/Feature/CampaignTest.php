<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\Contact;
use App\Models\SenderId;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CampaignTest extends TestCase
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
    public function test_it_creates_a_campaign_when_sending_to_a_group()
    {
        $group = Group::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by_user_id' => $this->user->id,
            'name' => 'Test Group'
        ]);

        Contact::create([
            'tenant_id' => $this->tenant->id,
            'created_by_user_id' => $this->user->id,
            'group_id' => $group->id,
            'name' => 'John Doe',
            'phone' => '+233241111111'
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Messaging\SendSms::class)
            ->set('targetType', 'group')
            ->set('selectedGroupId', $group->id)
            ->set('sender_id', 'ALERTS')
            ->set('message', 'Hello {{first_name}}')
            ->call('sendSms');

        $this->assertDatabaseHas('campaigns', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'total_recipients' => 1
        ]);

        $campaign = Campaign::first();
        $this->assertDatabaseHas('messages', [
            'campaign_id' => $campaign->id,
            'recipient' => '+233241111111'
        ]);
    }

    /** @test */
    public function test_it_can_render_campaign_list()
    {
        Campaign::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'name' => 'Promo Campaign',
            'sender_id' => 'ALERTS',
            'message_body' => 'Test message',
            'total_recipients' => 10,
            'total_cost' => 10,
            'status' => 'completed'
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Messaging\Campaigns::class)
            ->assertSee('Promo Campaign')
            ->assertSee('10');
    }
}
