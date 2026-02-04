<?php

namespace Tests\Feature\Billing;

use App\Jobs\SendBulkSmsJob;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deducts_credits_and_logs_transaction_on_success()
    {
        // 1. Setup
        $tenant = Tenant::factory()->create(['sms_credits' => 10]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        $campaign = Campaign::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => 'Billing Test',
            'sender_id' => 'TestSender',
            'message_body' => 'Test Message',
            'status' => 'pending',
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'recipient' => '1234567890',
            'body' => 'Test Message',
            'status' => 'pending'
        ]);

        // 2. Mock Service
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('calculateParts')->andReturn(2); // 2 credits cost
            $mock->shouldReceive('send')->once()->andReturn(['status' => 'success', 'sid' => 'test_id']);
        });

        // 3. Run Job
        $job = new SendBulkSmsJob(['1234567890'], 'Test Message', 'Sender', $campaign->id, [$message->id]);
        $job->handle(app(SmsService::class));

        // 4. Assert
        $tenant->refresh();
        $this->assertEquals(8, $tenant->sms_credits); // 10 - 2

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'usage',
            'amount' => 2,
            'balance_after' => 8,
        ]);
    }

    public function test_it_fails_job_if_insufficient_credits()
    {
         // 1. Setup
         $tenant = Tenant::factory()->create(['sms_credits' => 1]); // Only 1 credit
         $user = User::factory()->create(['tenant_id' => $tenant->id]);
         
         $campaign = Campaign::create([
             'tenant_id' => $tenant->id,
             'user_id' => $user->id,
             'name' => 'Billing Fail Test',
             'sender_id' => 'TestSender',
             'message_body' => 'Test Message',
             'status' => 'pending',
         ]);
 
         $message = Message::create([
             'user_id' => $user->id,
             'campaign_id' => $campaign->id,
             'recipient' => '1234567890',
             'body' => 'Test Message',
             'status' => 'pending'
         ]);
 
         // 2. Mock Service
         $this->mock(SmsService::class, function ($mock) {
             $mock->shouldReceive('calculateParts')->andReturn(2); // Cost is 2
             $mock->shouldNotReceive('send'); // Should NOT try to send
         });
 
         // 3. Run Job
         $job = new SendBulkSmsJob(['1234567890'], 'Test Message', 'Sender', $campaign->id, [$message->id]);
         $job->handle(app(SmsService::class));
 
         // 4. Assert
         $tenant->refresh();
         $this->assertEquals(1, $tenant->sms_credits); // Unchanged
 
         $this->assertDatabaseMissing('transactions', [
             'type' => 'usage',
         ]);

         $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => 'failed', 
         ]);
    }

    public function test_it_refunds_credits_if_gateway_fails()
    {
        // 1. Setup
        $tenant = Tenant::factory()->create(['sms_credits' => 10]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        $campaign = Campaign::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => 'Refund Test',
            'sender_id' => 'TestSender',
            'message_body' => 'Test Message',
            'status' => 'pending',
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'recipient' => '1234567890',
            'body' => 'Test Message',
            'status' => 'pending'
        ]);

        // 2. Mock Service
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('calculateParts')->andReturn(1);
            $mock->shouldReceive('send')->once()->andReturn(['status' => 'error', 'message' => 'Gateway Down']);
        });

        // 3. Run Job
        $job = new SendBulkSmsJob(['1234567890'], 'Test Message', 'Sender', $campaign->id, [$message->id]);
        $job->handle(app(SmsService::class));

        // 4. Assert
        $tenant->refresh();
        $this->assertEquals(10, $tenant->sms_credits); // 10 - 1 + 1 (refund)

        $this->assertDatabaseHas('transactions', [
            'type' => 'usage',
            'amount' => 1,
        ]);
        
        $this->assertDatabaseHas('transactions', [
            'type' => 'refund',
            'amount' => 1,
        ]);
    }
}
