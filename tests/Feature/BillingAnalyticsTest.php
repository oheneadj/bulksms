<?php

namespace Tests\Feature;

use App\Livewire\Messaging\CampaignAnalytics;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_own_invoice()
    {
        $user = User::factory()->create();
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 100,
            'reference' => 'REF123',
            'description' => 'Test',
            'balance_after' => 100
        ]);

        $response = $this->actingAs($user)
            ->get(route('billing.invoice', $transaction->id));

        $response->assertStatus(200);
        $response->assertSee('REF123');
    }

    public function test_cannot_view_others_invoice()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transaction = Transaction::create([
            'user_id' => $otherUser->id,
            'type' => 'deposit',
            'amount' => 100,
            'reference' => 'REF456',
            'description' => 'Test',
            'balance_after' => 100
        ]);

        $response = $this->actingAs($user)
            ->get(route('billing.invoice', $transaction->id));

        $response->assertStatus(403);
    }

    public function test_can_view_campaign_analytics()
    {
        $user = User::factory()->create();
        $campaign = Campaign::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'name' => 'Test Campaign',
            'sender_id' => 'Test',
            'message_body' => 'Test',
            'total_recipients' => 10,
            'total_cost' => 1,
            'status' => 'completed'
        ]);

        Livewire::actingAs($user)
            ->test(CampaignAnalytics::class, ['campaign' => $campaign])
            ->assertStatus(200)
            ->assertSee('Test Campaign');
    }

    public function test_can_export_campaign_report()
    {
        $user = User::factory()->create();
        $campaign = Campaign::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'name' => 'Test Campaign',
            'sender_id' => 'Test',
            'message_body' => 'Test',
            'total_recipients' => 1,
            'total_cost' => 1,
            'status' => 'completed'
        ]);
        
        Message::create([
            'user_id' => $user->id, // Message model uses user_id not campaign_id in previous steps? Wait, I added relationships.
            'campaign_id' => $campaign->id,
            'recipient' => '+1234567890',
            'body' => 'Test',
            'parts' => 1,
            'cost' => 1,
            'status' => 'delivered'
        ]);

        Livewire::actingAs($user)
            ->test(CampaignAnalytics::class, ['campaign' => $campaign])
            ->call('exportCsv')
            ->assertFileDownloaded('campaign_' . $campaign->id . '_report.csv');
    }
}
