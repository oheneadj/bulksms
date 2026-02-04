<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MessageHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_history_page_displays_messages(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Message::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'recipient' => '+447700900123',
            'body' => 'Test message',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'delivered',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/messaging/history');

        $response->assertStatus(200);
        $response->assertSee('Test message');
        $response->assertSee('+447700900123');
    }

    public function test_search_filters_messages(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Message::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'recipient' => '+447700900123',
            'body' => 'Hello John',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'delivered',
        ]);

        Message::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'recipient' => '+447700900456',
            'body' => 'Hello Jane',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'delivered',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MessageHistory::class)
            ->set('search', 'John')
            ->assertSee('Hello John')
            ->assertDontSee('Hello Jane');
    }

    public function test_status_filter_works(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Message::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'recipient' => '+447700900123',
            'body' => 'Delivered message',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'delivered',
        ]);

        Message::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'recipient' => '+447700900456',
            'body' => 'Failed message',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'failed',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MessageHistory::class)
            ->set('statusFilter', 'delivered')
            ->assertSee('Delivered message')
            ->assertDontSee('Failed message');
    }

    public function test_stats_calculate_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        // Create 3 delivered, 1 failed
        for ($i = 0; $i < 3; $i++) {
            Message::create([
                'user_id' => $user->id,
                'sender_id' => 'TEST',
                'recipient' => '+447700900' . $i,
                'body' => 'Message ' . $i,
                'parts' => 1,
                'cost' => 0.05,
                'status' => 'delivered',
            ]);
        }

        Message::create([
            'user_id' => $user->id,
            'sender_id' => 'TEST',
            'recipient' => '+447700900999',
            'body' => 'Failed',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'failed',
        ]);

        $response = $this->actingAs($user)->get('/messaging/history');

        $response->assertSee('75%'); // 3/4 = 75% delivery rate
        $response->assertSee('$0.20'); // 4 * 0.05 = 0.20 total cost
    }

    public function test_clear_filters_resets_all_filters(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MessageHistory::class)
            ->set('search', 'test')
            ->set('statusFilter', 'delivered')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', '');
    }
}
