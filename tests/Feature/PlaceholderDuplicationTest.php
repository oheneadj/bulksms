<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Message;
use App\Models\User;
use App\Models\SenderId;
use App\Services\SmsService;
use App\Jobs\SendMessageJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class PlaceholderDuplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_duplicate_title_with_title_and_firstname_tags()
    {
        $tenant = \App\Models\Tenant::create(['name' => 'Test Tenant', 'slug' => 'test-tenant', 'email' => 'test@example.com']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        Contact::create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $user->id,
            'title' => 'Mr',
            'first_name' => 'John',
            'surname' => 'Doe',
            'phone' => '+233555555555'
        ]);

        SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'TESTUSER',
            'status' => 'active', 
            'purpose' => 'Testing'
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'recipient' => '+233555555555',
            'body' => '{{title}}, {{first_name}}' . "\n" . 'Please wake up and talk to Nana. Your future depends on it!',
            'sender_id' => 'TESTUSER',
            'status' => 'pending'
        ]);

        $smsService = Mockery::mock(SmsService::class);
        $smsService->shouldReceive('send')
            ->once()
            ->withArgs(function ($recipient, $body) {
                // Expect: "Mr, John\nPlease wake up..."
                // NOT "Mr, Mr John"
                return str_contains($body, 'Mr, John') && !str_contains($body, 'Mr, Mr');
            });

        $job = new SendMessageJob($message);
        $job->handle($smsService);
    }

    public function test_it_duplicates_title_if_using_title_and_name_tags()
    {
        // This confirms the behavior if the user MISTAKENLY used {{name}}
        $tenant = \App\Models\Tenant::create(['name' => 'Test Tenant 2', 'slug' => 'test-tenant-2', 'email' => 'test2@example.com']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        Contact::create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $user->id,
            'title' => 'Mr',
            'first_name' => 'John',
            'surname' => 'Doe',
            'phone' => '+233555555555'
        ]);

        SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'TESTUSER',
            'status' => 'active', 
            'purpose' => 'Testing'
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'recipient' => '+233555555555',
            'body' => 'Hello {{title}} {{name}}', 
            'sender_id' => 'TESTUSER',
            'status' => 'pending'
        ]);

        $smsService = Mockery::mock(SmsService::class);
        $smsService->shouldReceive('send')
            ->once()
            ->withArgs(function ($recipient, $body) {
                // Expect: "Hello Mr Mr John Doe" because {{name}} accessor includes title
                return $body === 'Hello Mr Mr John Doe';
            });

        $job = new SendMessageJob($message);
        $job->handle($smsService);
    }
}
