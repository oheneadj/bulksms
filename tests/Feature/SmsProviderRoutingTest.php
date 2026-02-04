<?php

namespace Tests\Feature;

use App\Services\SmsService;
use App\Models\SmsProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class SmsProviderRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_defaults_to_mnotify_for_international_numbers()
    {
        // Create mNotify (Low priority but default target)
        SmsProvider::create([
            'name' => 'mNotify',
            'provider' => 'mnotify',
            'is_active' => true,
            'priority' => 1,
            'config' => ['key' => 'test_key', 'sender_id' => 'Default']
        ]);

        // Create Twilio (Active, but should not be picked by default logic anymore unless fallback fails)
        // Wait, if I changed the query to `where('provider', 'mnotify')`, it forces mnotify.
        // What if mnotify doesn't exist? The code has `?? ...->first()`. 
        // So validation: 
        // 1. If mNotify exists, use it.
        // 2. If mNotify does NOT exist, use whatever is active (e.g. Twilio).
        
        SmsProvider::create([
            'name' => 'Twilio',
            'provider' => 'twilio',
            'is_active' => true,
            'priority' => 10, // Higher priority shouldn't matter for the specific 'mnotify' query
            'config' => ['sid' => 'AC123', 'token' => 'token', 'from' => 'TwilioSender']
        ]);

        Http::fake([
            'api.mnotify.com/*' => Http::response(['status' => 'success', 'summary' => ['_id' => '123']], 200)
        ]);
        
        // We expect log for mNotify, NOT Twilio
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'mNotify');
            });

        $service = new SmsService();
        // Send to US Number
        $service->send('+15551234567', 'Test Body');
    }

    public function test_it_falls_back_if_mnotify_missing()
    {
        // Only Twilio exists
        SmsProvider::create([
            'name' => 'Twilio',
            'provider' => 'twilio',
            'is_active' => true,
            'priority' => 10,
            'config' => ['sid' => 'AC123', 'token' => 'token', 'from' => 'TwilioSender']
        ]);

        // Expect Twilio Log (Service catches exception because fake client fails, but log happens)
        // Or we can just check returned status if we don't mock Log strictly.
        // Use Log check for consistency.
        
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Twilio');
            });
            
        Log::shouldReceive('error')->times(1); // Expect error from Twilio send failure

        $service = new SmsService();
        $service->send('+15551234567', 'Test Body');
    }
}
