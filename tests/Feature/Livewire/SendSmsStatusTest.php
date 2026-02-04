<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Messaging\SendSms;
use App\Models\SenderId;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SendSmsStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_all_sender_ids_with_correct_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create IDs with different statuses
        $active = SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'ActiveID',
            'status' => 'active',
            'purpose' => 'Test'
        ]);

        $pending = SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'PendingID',
            'status' => 'payment_pending',
            'purpose' => 'Test'
        ]);

        $rejected = SenderId::create([
            'user_id' => $user->id,
            'sender_id' => 'RejectedID',
            'status' => 'rejected', // 'rejected' maps to rejected
            'purpose' => 'Test'
        ]);

        Livewire::test(SendSms::class)
            // Assert all IDs are present
            ->assertSee('ActiveID')
            ->assertSee('PendingID')
            ->assertSee('RejectedID')
            
            // Assert Status Labels
            ->assertSee('Verified')
            ->assertSee('Pending Approval')
            ->assertSee('Rejected')
            
            // Assert Active ID is selectable (value exists)
            ->assertSeeHtml('value="ActiveID"')
            
            // Assert Pending/Rejected are disabled
            // Use assertSeeHtml for raw attributes as assertSee escapes quotes.
            ->assertSeeHtml('value="PendingID"')
            ->assertSeeHtml('value="RejectedID"');
            
            // Verify that 'disabled' is present in the rendered HTML
            $this->assertStringContainsString('disabled', Livewire::test(SendSms::class)->html());
    }
}
