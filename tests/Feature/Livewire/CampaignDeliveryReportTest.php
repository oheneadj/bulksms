<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Messaging\Campaigns;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CampaignDeliveryReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_campaign_status_from_provider()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        $this->actingAs($user);

        $campaign = Campaign::create([
            'tenant_id' => $user->tenant_id, // assuming user factory sets this or we need a tenant
            'user_id' => $user->id,
            'name' => 'Test Campaign',
            'sender_id' => 'TestSender',
            'message_body' => 'Hello World',
            'total_recipients' => 1,
            'status' => 'sending', 
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'sender_id' => 'TestSender',
            'recipient' => '233241234567',
            'body' => 'Hello World',
            'parts' => 1,
            'status' => 'pending',
            'gateway_message_id' => 'CAMPAIGN-123', // Shared Campaign ID
            'sent_at' => now(),
        ]);

        // 2. Mock SmsService (Partial Mock to test logic in syncCampaignStatus but mock the API call)
        $this->partialMock(SmsService::class, function ($mock) {
            $mock->shouldReceive('getCampaignDeliveryReport')
                ->with('CAMPAIGN-123')
                ->once()
                ->andReturn([
                    'status' => 'success',
                    'report' => [
                        [
                            '_id' => 60711577,
                            'recipient' => '233241234567',
                            'status' => 'DELIVERED', // Should map to 'delivered'
                            'date_sent' => '2025-01-01 10:00:00',
                        ]
                    ]
                ]);
        });

        // 3. Run Livewire Action
        Livewire::test(Campaigns::class)
            ->call('syncStatus', $campaign->id)
            ->assertDispatched('toastMagic', status: 'success'); // Check for success toast

        // 4. Assertions
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => 'delivered',
        ]);
        
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'status' => 'completed', // Should auto-complete since all messages are delivered
        ]);
    }
}
