<?php

namespace Tests\Feature;

use App\Models\SenderId;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SenderIdWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_paystack_webhook_activates_sender_id()
    {
        $user = User::factory()->create();
        $senderId = SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'WEBHOOKID',
            'purpose' => 'Testing Webhook',
            'status' => 'payment_pending',
        ]);

        $reference = 'REF-WEBHOOK-' . rand(1000, 9999);

        // Mock Paystack Service verifyWebhookSignature
        $mockPaystack = Mockery::mock(PaystackService::class);
        $mockPaystack->shouldReceive('verifyWebhookSignature')
            ->once()
            ->andReturn(true);
            
         // We do NOT expect verifyTransaction to be called because Webhook payload has all data
         // Check handlePaystackWebhook logic: it does not call verifyTransaction!
         // It validates signature, then trusts payload.
         // Wait, handlePaystackCallback calls verifyTransaction. handlePaystackWebhook relies on signature.
         
        $this->app->instance(PaystackService::class, $mockPaystack);

        $payload = [
            'event' => 'charge.success',
            'data' => [
                'status' => 'success',
                'reference' => $reference,
                'amount' => 5000, // 50.00 GHS
                'metadata' => [
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'payment_type' => 'sender_id',
                    'sender_id_record' => $senderId->id,
                ]
            ]
        ];

        $response = $this->postJson(route('webhooks.paystack'), $payload, [
            'x-paystack-signature' => 'valid-signature' // mock service ignores actual value, returns true
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Verify Status Change
        $this->assertEquals('active', $senderId->refresh()->status);

        // Verify Transaction Record
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'purchase',
            'reference' => $reference,
            'description' => 'Sender ID Activation: WEBHOOKID via Paystack'
        ]);
    }
}
