<?php

namespace Tests\Unit;

use App\Models\SmsProvider;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SmsRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SmsService();
    }

    #[Test]
    public function it_routes_ghana_numbers_to_local_provider()
    {
        // Create Providers
        $mnotify = SmsProvider::factory()->create([
            'provider' => 'mnotify',
            'priority' => 10,
            'is_active' => true,
        ]);

        $twilio = SmsProvider::factory()->create([
            'provider' => 'twilio',
            'priority' => 5,
            'is_active' => true,
        ]);

        // Reflection to access protected method
        $method = new \ReflectionMethod(SmsService::class, 'getProvider');
        $method->setAccessible(true);

        // Test +233
        $provider = $method->invoke($this->service, '+233244123456');
        $this->assertEquals('mnotify', $provider->provider);
        $this->assertEquals($mnotify->id, $provider->id);

        // Test 233 (no plus)
        $provider = $method->invoke($this->service, '233501234567');
        $this->assertEquals('mnotify', $provider->provider);
    }

    #[Test]
    public function it_routes_international_numbers_to_global_provider()
    {
        SmsProvider::factory()->create(['provider' => 'mnotify', 'is_active' => true]);
        $twilio = SmsProvider::factory()->create(['provider' => 'twilio', 'is_active' => true]);

        $method = new \ReflectionMethod(SmsService::class, 'getProvider');
        $method->setAccessible(true);

        // Test US Number (+1)
        $provider = $method->invoke($this->service, '+14155552671');
        $this->assertEquals('twilio', $provider->provider);
    }

    #[Test]
    public function it_falls_back_if_local_provider_is_inactive()
    {
        // Inactive mnotify
        SmsProvider::factory()->create(['provider' => 'mnotify', 'is_active' => false]);
        
        // Active Twilio
        $twilio = SmsProvider::factory()->create(['provider' => 'twilio', 'is_active' => true]);

        $method = new \ReflectionMethod(SmsService::class, 'getProvider');
        $method->setAccessible(true);

        // Should route to Twilio because mnotify is inactive
        $provider = $method->invoke($this->service, '+233244123456');
        $this->assertEquals('twilio', $provider->provider);
    }
    
    #[Test]
    public function it_returns_any_active_provider_if_default_global_is_missing()
    {
         // Only MessageBird Active
         $mb = SmsProvider::factory()->create(['provider' => 'messagebird', 'is_active' => true]);
         
         $method = new \ReflectionMethod(SmsService::class, 'getProvider');
         $method->setAccessible(true);
         
         $provider = $method->invoke($this->service, '+14155552671');
         $this->assertEquals('messagebird', $provider->provider);
    }
}
