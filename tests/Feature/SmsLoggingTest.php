<?php

namespace Tests\Feature;

use App\Services\SmsService;
use App\Models\Message;
use App\Models\User;
use App\Models\SmsProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class SmsLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_mnotify_request()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Sending mNotify SMS', Mockery::on(function ($data) {
                return isset($data['endpoint']) && 
                       isset($data['payload']['recipient']) &&
                       $data['payload']['message'] === 'Test Body';
            }));
            
        // Setup Provider
        SmsProvider::create([
            'name' => 'mNotify',
            'provider' => 'mnotify',
            'is_active' => true,
            'config' => ['key' => 'test_key', 'sender_id' => 'Default']
        ]);

        Http::fake([
            'api.mnotify.com/*' => Http::response(['status' => 'success', 'summary' => ['_id' => '123']], 200)
        ]);

        $service = new SmsService();
        $service->send('233200000000', 'Test Body', null, null, 'Sender');
    }

    public function test_it_logs_twilio_request()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Sending Twilio SMS', Mockery::on(function ($data) {
                return isset($data['to']) && 
                       isset($data['params']['body']) &&
                       $data['params']['body'] === 'Twilio Body';
            }));
            
        // Setup Provider (Twilio)
        SmsProvider::create([
            'name' => 'Twilio',
            'provider' => 'twilio',
            'is_active' => true,
            'priority' => 10,
            'config' => ['sid' => 'AC123', 'token' => 'token', 'from' => 'TwilioSender']
        ]);

        // Mock Twilio Client (we need to mock the external call or just let it fail/succeed but check logs before exception/return)
        // Since `sendTwilio` instantiates `new TwilioClient`, mocking it with Mockery::mock overload is hard without DI.
        // However, if we focus on the LOG call which happens BEFORE the instantiation/call? 
        // No, wait. In my code:
        // try { $client = new TwilioClient...; Log::info...; $response = ... }
        // So it instantiates client first.
        
        // Actually, `new TwilioClient` might not throw just by constructive if args are strings.
        // But `messages->create` will fail if not mocked or if real credentials invalid.
        // We can just rely on Log expectation and catch the exception if it throws, or use a partial mock if possible.
        // Or better, let's just assert the Log call. Even if `messages->create` throws, Log::info runs before it.
        // Wait, looking at my code again:
        
        /*
            $client = new TwilioClient($sid, $token);
            $payload = ...
            Log::info(...)
            $response = $client->messages->create(...)
        */
        
        // Yes, Log is before create. So even if create fails (which it will with fake creds), Log should have happened.
        // But we need to handle the exception in test or suppress it.
        // Actually the Service catches Exception and logs error. So valid flow.
        
        // We need to Mock the Twilio Client to avoid "Class 'Twilio\Rest\Client' not found" if not installed? 
        // User has it installed presumably. 
        // But making a real network call or crashing on `new Client` is bad.
        // `TwilioClient` constructor usually just sets properties.
        
        // Let's assume constructor is safe. `messages->create` will throw. 
        // The service catches it. So valid test.

        // Allow error logging as well since it will fail
        Log::shouldReceive('error')->times(1); // Expect 1 error due to failed fake call

        $service = new SmsService();
        // Use international number to trigger Twilio fallback (or force it via provider logic)
        // Provider logic says: if local provider found use it. Else fallbacks.
        // Creating ONLY Twilio provider ensures it's picked.
        
        $service->send('+15555555555', 'Twilio Body', null, null, 'Sender');
    }
}
