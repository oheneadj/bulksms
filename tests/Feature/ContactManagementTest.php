<?php

namespace Tests\Feature;

use App\Jobs\SendMessageJob;
use App\Livewire\Contacts;
use App\Livewire\GroupDetails;
use App\Models\Contact;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_contact_to_group_via_details_component()
    {
        $user = User::factory()->create();
        $group = Group::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Group',
            'created_by' => $user->id
        ]);
        $contact = Contact::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test',
            'phone' => '+1234567890'
        ]);

        Livewire::actingAs($user)
            ->test(GroupDetails::class, ['group' => $group])
            ->set('showAddModal', true)
            ->set('selectedContacts', [$contact->id])
            ->call('addContacts');

        $this->assertTrue($group->contacts()->where('id', $contact->id)->exists());
    }

    public function test_can_remove_contact_from_group_via_details_component()
    {
        $user = User::factory()->create();
        $group = Group::create(['tenant_id' => $user->tenant_id, 'name' => 'Test Group', 'created_by' => $user->id]);
        $contact = Contact::create(['tenant_id' => $user->tenant_id, 'name' => 'Test', 'phone' => '+1234567890', 'group_id' => $group->id]);

        Livewire::actingAs($user)
            ->test(GroupDetails::class, ['group' => $group])
            ->call('removeContact', $contact->id);

        $this->assertFalse($group->contacts()->where('id', $contact->id)->exists());
    }

    public function test_can_unsubscribe_and_reactivate_contact()
    {
        $user = User::factory()->create();
        $contact = Contact::create(['tenant_id' => $user->tenant_id, 'name' => 'Test', 'phone' => '+1234567890']);

        Livewire::actingAs($user)
            ->test(Contacts::class)
            ->call('unsubscribe', $contact->id);

        $this->assertTrue($contact->fresh()->is_unsubscribed);
        $this->assertNotNull($contact->fresh()->unsubscribed_at);

        Livewire::actingAs($user)
            ->test(Contacts::class)
            ->call('reactivate', $contact->id);

        $this->assertFalse($contact->fresh()->is_unsubscribed);
        $this->assertNull($contact->fresh()->unsubscribed_at);
    }

    public function test_send_message_job_skips_unsubscribed_contacts()
    {
        $user = User::factory()->create();
        $contact = Contact::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test',
            'phone' => '+1234567890',
            'is_unsubscribed' => true
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'sender_id' => 'Test',
            'recipient' => '+1234567890',
            'body' => 'Test message',
            'parts' => 1,
            'cost' => 1,
            'status' => 'queued'
        ]);

        $mockService = \Mockery::mock(SmsService::class);
        $mockService->shouldNotReceive('send');

        $job = new SendMessageJob($message);
        $job->handle($mockService);

        $this->assertEquals('failed', $message->fresh()->status);
        $this->assertEquals('SKIPPED-UNSUBSCRIBED', $message->fresh()->gateway_message_id);
    }
}
