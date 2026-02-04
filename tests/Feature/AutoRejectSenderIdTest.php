<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\SenderId;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutoRejectSenderIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_auto_rejects_sender_id_on_gateway_error()
    {
        $user = User::factory()->create();
        $senderId = SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'BadSender',
            'status' => 'active',
            'purpose' => 'Testing'
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'sender_id' => 'BadSender',
            'recipient' => '1234567890',
            'body' => 'Test Body',
            'status' => 'queued'
        ]);

        // Mock Http to return the specific error
        Http::fake([
            'api.mnotify.com/*' => Http::response([
                'status' => 'error', // Or whatever mNotify returns on top level, sometimes they return 200 with error body? 
                // Based on log: "Mnotify Error: Unknown error {... 'response': {'error': 'sender id is not approved...'}}"
                // The log code says: $data = $response->json(); ... Log::error("..." . $data['error'] ...)
                // So the JSON body has "error".
                'error' => 'sender id is not registered or approved. please contact our support team'
            ], 200) // Usually 200 OK even for business logic errors in some APIs, or 400. Code handles unsuccessful too.
        ]);
        
        // Ensure provider exists (SmsService usually checks this)
        // We might need to seed SmsProvider or use a mock. 
        // Using real SmsService to test the integration of logic.
        \App\Models\SmsProvider::create([
            'name' => 'mNotify',
            'provider' => 'mnotify',
            'is_active' => true,
            'config' => ['key' => 'test_key', 'sender_id' => 'Default']
        ]);

        $service = new SmsService();
        $service->send(
            $message->recipient,
            $message->body,
            $message->id,
            null,
            $message->sender_id 
        );

        // Assert SenderId is now rejected
        $this->assertEquals('rejected', $senderId->fresh()->status);
        $this->assertStringContainsString('Automatically rejected', $senderId->fresh()->reason);
    }
}
