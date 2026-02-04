<?php

namespace Tests\Feature;

use App\Services\SmsService;
use App\Models\Message;
use App\Models\User;
use App\Models\Contact;
use App\Models\Tenant; // Assuming Tenant model exists
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;
use App\Jobs\SendMessageJob;

class PlaceholderReplacementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_replaces_placeholders_with_contact_data()
    {
        // Setup User and Tenant
        // Assuming Tenant model based on code usage
        $user = User::factory()->create();
        $user->tenant_id = 1; // Default tenant
        $user->save();
        
        // Setup Contact
        $contact = Contact::create([
            'tenant_id' => 1,
            'created_by_user_id' => $user->id,
            'title' => 'Mr',
            'first_name' => 'John',
            'surname' => 'Doe',
            'phone' => '+233555555555',
            'email' => 'john@test.com'
        ]);

        // Setup Active Sender ID
        \App\Models\SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'TESTUSER',
            'status' => 'active',
            'purpose' => 'Testing'
        ]);

        // Setup Message
        $message = Message::create([
            'user_id' => $user->id,
            'recipient' => '+233555555555',
            'body' => 'Hello {{title}} {{surname}}, full name: {{name}}',
            'sender_id' => 'TESTUSER',
            'status' => 'pending'
        ]);

        // Mock SmsService
        $smsService = Mockery::mock(SmsService::class);
        $smsService->shouldReceive('send')
            ->once()
            ->withArgs(function ($recipient, $body, $id, $parts, $senderId) {
                return $recipient === '+233555555555' &&
                       str_contains($body, 'Hello Mr Doe') && 
                       str_contains($body, 'full name: Mr John Doe');
            });

        // Run Job
        $job = new SendMessageJob($message);
        $job->handle($smsService);
    }
}
