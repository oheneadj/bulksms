<?php

namespace Tests\Feature;

use App\Jobs\SendBulkSmsJob;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\SenderId;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SenderIdFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fails_job_if_sender_id_is_not_active()
    {
        // 1. Setup
        $tenant = Tenant::factory()->create(['sms_credits' => 100]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        // Create a Pending Sender ID
        SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'PendingID',
            'status' => 'pending',
            'purpose' => 'Testing'
        ]);

        $campaign = Campaign::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => 'Sender ID Test',
            'sender_id' => 'PendingID',
            'message_body' => 'Test Body',
            'status' => 'pending',
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'recipient' => '1234567890',
            'body' => 'Test Body',
            'status' => 'pending'
        ]);

        // 2. Mock Service (Should NOT be called for sending)
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('calculateParts')->andReturn(1);
            $mock->shouldNotReceive('send');
        });

        // 3. Run Job
        $job = new SendBulkSmsJob(['1234567890'], 'Test Body', 'PendingID', $campaign->id, [$message->id]);
        $job->handle(app(SmsService::class));

        // 4. Assert
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => 'failed', 
        ]);
        // status should be failed with a reason? (We don't store reason on message yet other than 'failed')
    }

    public function test_it_sends_successfully_if_sender_id_is_active()
    {
        // 1. Setup
        $tenant = Tenant::factory()->create(['sms_credits' => 100]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        // Create Active Sender ID
        SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'ActiveID',
            'status' => 'active',
            'purpose' => 'Testing'
        ]);

        $campaign = Campaign::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => 'Sender ID Test',
            'sender_id' => 'ActiveID',
            'message_body' => 'Test Body',
            'status' => 'pending',
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'recipient' => '1234567890',
            'body' => 'Test Body',
            'status' => 'pending'
        ]);

        // 2. Mock Service
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('calculateParts')->andReturn(1);
            // Expect send to be called WITH 'ActiveID'
            $mock->shouldReceive('send')
                ->once()
                ->with(
                    ['1234567890'], 
                    'Test Body', 
                    null, 
                    null, 
                    'ActiveID'
                )
                ->andReturn(['status' => 'success', 'sid' => 'test_id']);
        });

        // 3. Run Job
        $job = new SendBulkSmsJob(['1234567890'], 'Test Body', 'ActiveID', $campaign->id, [$message->id]);
        $job->handle(app(SmsService::class));

        // 4. Assert
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => 'sent', 
        ]);
    }
}
