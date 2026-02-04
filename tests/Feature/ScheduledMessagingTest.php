<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\SenderId;
use App\Models\Message;
use App\Console\Commands\ProcessScheduledMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduledMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_schedule_a_message(): void
    {
        $tenant = Tenant::factory()->create(['sms_credits' => 100]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        // Ensure SmsService is mocked to avoid external calls or credit checks failure
        $this->mock(\App\Services\SmsService::class, function ($mock) {
            $mock->shouldReceive('calculateParts')->andReturn(1);
        });

        SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'status' => 'approved',
        ]);

        $scheduledTime = now()->addHours(2)->format('Y-m-d\TH:i');

        \Devrabiul\ToastMagic\Facades\ToastMagic::spy();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Messaging\SendSms::class)
            ->set('sender_id', 'TEST')
            ->set('recipients', '+447700900123')
            ->set('message', 'Test scheduled message')
            ->set('schedule', true)
            ->set('scheduledAt', $scheduledTime)
            ->call('sendSms')
            ->assertHasNoErrors();
        
        \Devrabiul\ToastMagic\Facades\ToastMagic::shouldHaveReceived('success');
    }

    public function test_scheduled_messages_are_processed_when_due(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $message = Message::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'recipient' => '+447700900123',
            'body' => 'Test message',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinutes(5), 
        ]);

        // Mock the service to prevent failure status update
        $this->mock(\App\Services\SmsService::class, function ($mock) {
            $mock->shouldReceive('send')->once()->andReturn(['status' => 'success']);
        });

        $this->artisan('sms:process-scheduled')
            ->expectsOutput('Processing 1 scheduled messages...')
            ->assertExitCode(0);

        // Should be queued (command sets it to queued before dispatching job)
        // Since we are using sync queue driver in tests, the job runs immediately.
        // If the mocked service says success, it might update to 'sent' if the job logic does so.
        // But logic in SmsService updateMessageStatus sets it to 'sent' only if we call updateMessageStatus.
        // Our mock returns array but doesn't side-effect updateMessageStatus unless we mock that too or rely on Job handling it.
        // The Job calls $smsService->send. It returns result. The Job doesn't update status?
        // Let's check SendMessageJob again... it just calls send.
        // SmsService::send -> updateMessageStatus('sent') [if internal]
        // Since we mocked send, we bypassed updateMessageStatus.
        // So status should remain 'queued' as set by the command.
        
        $this->assertEquals('queued', $message->fresh()->status);
    }

    public function test_future_scheduled_messages_are_not_processed(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $message = Message::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'recipient' => '+447700900123',
            'body' => 'Future message',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'scheduled',
            'scheduled_at' => now()->addHours(2), // Future
        ]);

        $this->artisan('sms:process-scheduled')
            ->expectsOutput('No scheduled messages due.')
            ->assertExitCode(0);

        // Status should remain scheduled
        $this->assertEquals('scheduled', $message->fresh()->status);
    }

    public function test_schedule_validates_future_date(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'status' => 'approved',
        ]);

        // Use a clearer past date format
        $pastTime = now()->subHours(2)->format('Y-m-d\TH:i');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Messaging\SendSms::class)
            ->set('schedule', true)
            ->set('scheduledAt', $pastTime) // Set invalid time
            ->set('sender_id', 'TEST')
            ->set('recipients', '+447700900123')
            ->set('message', 'Test')
            ->call('sendSms')
            ->assertHasErrors(['scheduledAt']);
    }
}
