<?php

namespace Tests\Feature;

use App\Livewire\Messaging\SenderIds;
use App\Models\SenderId;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SenderIdViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_shows_pay_button_for_payment_pending_status()
    {
        $user = User::factory()->create();
        $senderId = SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'TESTPAY',
            'purpose' => 'Testing Payment Button',
            'status' => 'payment_pending', // Force this status
        ]);

        Livewire::actingAs($user)
            ->test(SenderIds::class)
            ->assertSee('Pay to Activate')
            ->assertSee('TESTPAY');
    }

    public function test_view_hides_pay_button_for_pending_status()
    {
        $user = User::factory()->create();
        SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'TESTPEND',
            'purpose' => 'Testing Pending Status',
            'status' => 'pending', 
        ]);

        Livewire::actingAs($user)
            ->test(SenderIds::class)
            ->assertDontSee('Pay to Activate')
            ->assertSee('TESTPEND');
    }
}
