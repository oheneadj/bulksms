<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $tenant = \App\Models\Tenant::factory()->create(['id' => 1]); // Match the default in Contacts.php
        $this->user = User::factory()->create(['tenant_id' => $tenant->id]);
    }

    /** @test */
    public function test_it_can_download_the_import_template()
    {
        $this->actingAs($this->user)
            ->get(route('contacts'))
            ->assertStatus(200);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Contacts::class)
            ->call('downloadTemplate')
            ->assertStatus(200);
    }

    /** @test */
    public function test_it_validates_phone_numbers_during_manual_creation()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Contacts::class)
            ->set('name', 'John Doe')
            ->set('phone', 'invalid-phone')
            ->call('save')
            ->assertHasErrors(['phone']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Contacts::class)
            ->set('name', 'John Doe')
            ->set('phone', '+233241234567') // Valid
            ->call('save')
            ->assertHasNoErrors();
    }

    /** @test */
    public function test_it_can_reset_the_upload_field()
    {
        \Illuminate\Support\Facades\Storage::fake('imports');
        $file = \Illuminate\Http\UploadedFile::fake()->create('contacts.csv', 100);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Contacts::class)
            ->set('csvFile', $file)
            ->assertSet('csvFile', fn($v) => $v instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
            ->set('csvFile', null)
            ->assertSet('csvFile', null);
    }
}
