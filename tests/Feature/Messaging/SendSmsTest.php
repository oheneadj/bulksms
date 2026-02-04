<?php

namespace Tests\Feature\Messaging;

use App\Models\User;
use App\Models\SenderId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SendSmsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_render_the_send_sms_page()
    {
        $tenant = \App\Models\Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        $this->actingAs($user)
            ->get(route('messaging.send'))
            ->assertStatus(200)
            ->assertSeeLivewire('messaging.send-sms');
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $tenant = \App\Models\Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        Livewire::actingAs($user)
            ->test(\App\Livewire\Messaging\SendSms::class)
            ->call('sendSms')
            ->assertHasErrors(['sender_id', 'recipients', 'message']);
    }

    #[Test]
    public function it_shows_active_sender_ids()
    {
        $tenant = \App\Models\Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        SenderId::factory()->create([
            'user_id' => $user->id,
            'sender_id' => 'ACTIVE_ID',
            'status' => 'active'
        ]);

        SenderId::factory()->create([
            'user_id' => $user->id,
            'sender_id' => 'PENDING_ID',
            'status' => 'pending'
        ]);

        SenderId::factory()->create([
            'user_id' => $user->id,
            'sender_id' => 'PAYMENT_PENDING',
            'status' => 'payment_pending'
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Messaging\SendSms::class)
            ->assertSee('ACTIVE_ID')
            ->assertDontSee('PENDING_ID')
            ->assertDontSee('PAYMENT_PENDING');
    }

    #[Test]
    public function it_allows_sending_with_template_instead_of_message()
    {
        $tenant = \App\Models\Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        $template = \App\Models\MessageTemplate::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Template',
            'body' => 'Template Body'
        ]);

        $this->mock(\App\Services\SmsService::class, function ($mock) {
            $mock->shouldReceive('calculateParts')->andReturn(1);
            $mock->shouldReceive('send')->andReturn(['status' => 'success']);
        });

        \Devrabiul\ToastMagic\Facades\ToastMagic::spy();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Messaging\SendSms::class)
            ->set('sender_id', 'TEST')
            ->set('recipients', '+123456789')
            ->set('selectedTemplateId', $template->id)
            ->set('targetType', 'individual')
            ->call('sendSms')
            ->assertHasNoErrors(['message']);
            
        \Devrabiul\ToastMagic\Facades\ToastMagic::shouldHaveReceived('success');
    }
}
