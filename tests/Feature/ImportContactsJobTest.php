<?php

namespace Tests\Feature;

use App\Jobs\ImportContactsJob;
use App\Models\Contact;
use App\Models\Group;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportContactsJobTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = \App\Models\Tenant::factory()->create(['id' => 1]);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_imports_contacts_from_csv_and_sends_notification()
    {
        Notification::fake();
        Storage::fake('local');

        // Create CSV content with header and 2 rows
        $content = "name,phone,email,group\n";
        $content .= "John Doe,+1234567890,john@example.com,Customers\n";
        $content .= "Jane Doe,+1987654321,jane@example.com,Staff\n";

        // Store file
        $path = 'imports/test.csv';
        Storage::put($path, $content);

        // Run Job
        $job = new ImportContactsJob($path, $this->user->id, $this->tenant->id);
        $job->handle();

        // Verify Contacts
        $this->assertDatabaseHas('contacts', ['phone' => '+1234567890', 'name' => 'John Doe']);
        $this->assertDatabaseHas('contacts', ['phone' => '+1987654321', 'name' => 'Jane Doe']);
        $this->assertEquals(2, Contact::count());

        // Verify Groups
        $this->assertDatabaseHas('groups', ['name' => 'Customers']);
        $this->assertDatabaseHas('groups', ['name' => 'Staff']);
        
        $customerGroup = Group::where('name', 'Customers')->first();
        $this->assertEquals(1, $customerGroup->contacts_count, 'Customers group count should be 1');

        // Verify Notification
        Notification::assertSentTo(
            [$this->user],
            ImportCompletedNotification::class,
            function ($notification) {
                return $notification->importedCount === 2 && $notification->skippedCount === 0;
            }
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_skips_invalid_rows_and_tracks_stats()
    {
        Notification::fake();
        Storage::fake('local');

        $content = "name,phone\n";
        $content .= "Valid,+1234567890\n";
        $content .= "InvalidPhone,not-a-phone\n"; // Should skip
        $content .= "MissingPhone,\n"; // Should skip

        $path = 'imports/mixed.csv';
        Storage::put($path, $content);

        $job = new ImportContactsJob($path, $this->user->id, $this->tenant->id);
        $job->handle();

        $this->assertEquals(1, Contact::count());
        $this->assertDatabaseHas('contacts', ['name' => 'Valid']);

        Notification::assertSentTo(
            [$this->user],
            ImportCompletedNotification::class,
            function ($notification) {
                return $notification->importedCount === 1 && $notification->skippedCount === 2;
            }
        );
    }
}
