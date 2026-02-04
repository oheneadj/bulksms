# Testing Guide

Comprehensive testing documentation for the Bulk SMS/WhatsApp Application using Pest PHP.

---

## 📋 Table of Contents

1. [Testing Philosophy](#testing-philosophy)
2. [Setup](#setup)
3. [Running Tests](#running-tests)
4. [Test Types](#test-types)
5. [Writing Tests](#writing-tests)
6. [Testing Patterns](#testing-patterns)
7. [Common Test Scenarios](#common-test-scenarios)
8. [Continuous Integration](#continuous-integration)

---

## 🎯 Testing Philosophy

### Core Principles

1. **Test Every Component**: Every Livewire component, service, action, and model must have tests
2. **Test Behavior, Not Implementation**: Focus on what the code does, not how
3. **Readable Tests**: Tests are documentation - make them clear
4. **Fast Feedback**: Tests should run quickly
5. **Isolated Tests**: Each test should be independent

### Test Coverage Goals

- **Minimum**: 80% code coverage
- **Target**: 90%+ coverage
- **Critical paths**: 100% coverage (auth, payments, message sending)

---

## 🛠 Setup

### Install Pest

Already included in Laravel 12, but verify:

```bash
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev
```

### Initialize Pest

```bash
php artisan pest:install
```

### Configuration

`tests/Pest.php`:

```php
<?php

use App\Models\{User, Tenant};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class
)->in('Feature', 'Unit');

// Helper function: Create authenticated user
function actingAsTenantUser(?Tenant $tenant = null, string $role = 'user'): User
{
    $tenant ??= Tenant::factory()->create();
    $user = User::factory()
        ->for($tenant)
        ->create(['role' => $role]);
    
    test()->actingAs($user);
    
    return $user;
}

// Helper function: Create super admin
function actingAsSuperAdmin(): User
{
    $user = User::factory()->create([
        'role' => 'super_admin',
        'tenant_id' => null,
    ]);
    
    test()->actingAs($user);
    
    return $user;
}

// Helper: Expect tenant isolation
expect()->extend('toBeIsolatedToTenant', function (Tenant $tenant) {
    return $this->value->every(fn($model) => $model->tenant_id === $tenant->id);
});
```

---

## 🏃 Running Tests

### Run All Tests

```bash
# Using Artisan
php artisan test

# Using Pest directly
./vendor/bin/pest

# With coverage
php artisan test --coverage

# Parallel execution (faster)
php artisan test --parallel
```

### Run Specific Tests

```bash
# Specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Specific file
php artisan test tests/Feature/MessageSendingTest.php

# Specific test
php artisan test --filter="can send sms message"

# By group
php artisan test --group=messaging
```

### Watch Mode (Auto-run on changes)

```bash
./vendor/bin/pest --watch
```

---

## 📚 Test Types

### 1. Feature Tests

Test complete user workflows through HTTP requests.

**Location**: `tests/Feature/`

```php
// tests/Feature/ContactManagementTest.php
<?php

use App\Models\{Tenant, User, Contact};
use function Pest\Laravel\{get, post, delete};

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->for($this->tenant)->create();
    $this->actingAs($this->user);
});

test('user can view contacts list', function () {
    Contact::factory()->count(5)->for($this->tenant)->create();
    
    $response = get('/contacts');
    
    $response->assertOk()
        ->assertViewHas('contacts', fn($contacts) => $contacts->count() === 5);
});

test('user can create contact', function () {
    $response = post('/contacts', [
        'name' => 'John Doe',
        'phone' => '+233201234567',
    ]);
    
    $response->assertRedirect('/contacts');
    
    expect(Contact::where('phone', '+233201234567')->exists())->toBeTrue();
});

test('user cannot see other tenants contacts', function () {
    $otherTenant = Tenant::factory()->create();
    Contact::factory()->for($otherTenant)->create(['name' => 'Other Tenant Contact']);
    
    $response = get('/contacts');
    
    $response->assertDontSee('Other Tenant Contact');
});
```

---

### 2. Unit Tests

Test individual classes/methods in isolation.

**Location**: `tests/Unit/`

```php
// tests/Unit/Services/MessageServiceTest.php
<?php

use App\Services\MessageService;
use App\Models\{Tenant, Message, Contact};
use App\Enums\MessageChannel;

beforeEach(function () {
    $this->service = app(MessageService::class);
    $this->tenant = Tenant::factory()->create(['sms_credits' => 100]);
});

test('calculates credit cost correctly for sms', function () {
    $cost = $this->service->calculateCreditCost(
        channel: MessageChannel::SMS,
        recipientCount: 10
    );
    
    expect($cost)->toBe(10);
});

test('calculates credit cost correctly for both channels', function () {
    $cost = $this->service->calculateCreditCost(
        channel: MessageChannel::BOTH,
        recipientCount: 10
    );
    
    expect($cost)->toBe(20); // SMS + WhatsApp
});

test('throws exception when insufficient credits', function () {
    $this->tenant->update(['sms_credits' => 5]);
    
    $this->service->sendBulkMessage(
        tenant: $this->tenant,
        recipients: Contact::factory()->count(10)->make(),
        content: 'Test message'
    );
})->throws(\App\Exceptions\InsufficientCreditsException::class);
```

---

### 3. Livewire Component Tests

Test Livewire components.

**Location**: `tests/Feature/Livewire/`

```php
// tests/Feature/Livewire/CreateContactTest.php
<?php

use App\Livewire\Contacts\CreateContact;
use App\Models\{Tenant, User, Contact, Group};
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->for($this->tenant)->create();
    $this->actingAs($this->user);
});

test('component can be rendered', function () {
    livewire(CreateContact::class)
        ->assertOk();
});

test('can create contact', function () {
    livewire(CreateContact::class)
        ->set('form.name', 'John Doe')
        ->set('form.phone', '+233201234567')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect('/contacts');
    
    expect(Contact::where('phone', '+233201234567')->exists())->toBeTrue();
});

test('validates required fields', function () {
    livewire(CreateContact::class)
        ->set('form.name', '')
        ->set('form.phone', '')
        ->call('save')
        ->assertHasErrors(['form.name', 'form.phone']);
});

test('validates phone number format', function () {
    livewire(CreateContact::class)
        ->set('form.name', 'John Doe')
        ->set('form.phone', 'invalid-phone')
        ->call('save')
        ->assertHasErrors(['form.phone']);
});

test('can assign contact to group', function () {
    $group = Group::factory()->for($this->tenant)->create();
    
    livewire(CreateContact::class)
        ->set('form.name', 'John Doe')
        ->set('form.phone', '+233201234567')
        ->set('form.group_id', $group->id)
        ->call('save');
    
    $contact = Contact::where('phone', '+233201234567')->first();
    
    expect($contact->group_id)->toBe($group->id);
});

test('contact is scoped to tenant', function () {
    livewire(CreateContact::class)
        ->set('form.name', 'John Doe')
        ->set('form.phone', '+233201234567')
        ->call('save');
    
    $contact = Contact::where('phone', '+233201234567')->first();
    
    expect($contact->tenant_id)->toBe($this->tenant->id);
});
```

---

### 4. Browser Tests (Dusk)

End-to-end testing with a real browser.

**Location**: `tests/Browser/`

```bash
# Install Dusk
composer require --dev laravel/dusk
php artisan dusk:install
```

```php
// tests/Browser/SendMessageTest.php
<?php

namespace Tests\Browser;

use App\Models\{Tenant, User, Contact, SenderId};
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SendMessageTest extends DuskTestCase
{
    public function test_can_send_message_to_contacts()
    {
        $tenant = Tenant::factory()->create(['sms_credits' => 100]);
        $user = User::factory()->for($tenant)->create();
        $senderId = SenderId::factory()->for($tenant)->create(['status' => 'approved']);
        Contact::factory()->for($tenant)->count(3)->create();
        
        $this->browse(function (Browser $browser) use ($user, $senderId) {
            $browser->loginAs($user)
                    ->visit('/messages/send')
                    ->select('recipient_type', 'all_groups')
                    ->select('sender_id', $senderId->id)
                    ->select('channel', 'sms')
                    ->type('content', 'Test message')
                    ->press('Send Message')
                    ->assertPathIs('/messages')
                    ->assertSee('Message sent successfully');
        });
    }
}
```

---

## ✍️ Writing Tests

### Test Naming Convention

Use descriptive test names that read like sentences:

```php
// ✅ GOOD
test('admin can delete sender id they created', function () { /* ... */ });
test('user cannot delete sender id created by another user', function () { /* ... */ });
test('message is queued when sending to large recipient list', function () { /* ... */ });

// ❌ BAD
test('delete sender id', function () { /* ... */ });
test('test1', function () { /* ... */ });
```

---

### AAA Pattern (Arrange, Act, Assert)

```php
test('user can create contact', function () {
    // Arrange: Set up test data
    $tenant = Tenant::factory()->create();
    $user = User::factory()->for($tenant)->create();
    $this->actingAs($user);
    
    // Act: Perform the action
    $response = post('/contacts', [
        'name' => 'John Doe',
        'phone' => '+233201234567',
    ]);
    
    // Assert: Verify the outcome
    $response->assertRedirect('/contacts');
    expect(Contact::where('phone', '+233201234567')->exists())->toBeTrue();
});
```

---

### Use Factories

Always use factories for test data:

```php
// ❌ BAD: Manual creation
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'role' => 'user',
    'tenant_id' => 1,
]);

// ✅ GOOD: Factory
$user = User::factory()->create();

// ✅ BETTER: Factory with specific attributes
$user = User::factory()->for($tenant)->create(['role' => 'tenant_admin']);
```

---

### Test Data Builders

For complex scenarios, use data builders:

```php
// tests/Builders/MessageBuilder.php
class MessageBuilder
{
    private array $data = [];
    
    public function forTenant(Tenant $tenant): self
    {
        $this->data['tenant_id'] = $tenant->id;
        return $this;
    }
    
    public function withRecipients(int $count): self
    {
        $this->data['recipients'] = Contact::factory()->count($count)->create();
        return $this;
    }
    
    public function viaChannel(MessageChannel $channel): self
    {
        $this->data['channel'] = $channel;
        return $this;
    }
    
    public function build(): Message
    {
        return Message::factory()->create($this->data);
    }
}

// Usage in tests
test('tracks message delivery correctly', function () {
    $tenant = Tenant::factory()->create();
    
    $message = (new MessageBuilder())
        ->forTenant($tenant)
        ->withRecipients(10)
        ->viaChannel(MessageChannel::SMS)
        ->build();
    
    // Test logic...
});
```

---

## 🎨 Testing Patterns

### 1. Tenant Isolation Testing

**CRITICAL**: Every tenant-scoped feature must be tested for isolation.

```php
test('user cannot access other tenants data', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    
    $user1 = User::factory()->for($tenant1)->create();
    $contact1 = Contact::factory()->for($tenant1)->create();
    $contact2 = Contact::factory()->for($tenant2)->create();
    
    actingAs($user1);
    
    $contacts = Contact::all();
    
    expect($contacts)->toHaveCount(1)
        ->and($contacts->first()->id)->toBe($contact1->id);
});

test('super admin can access all tenants data', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    
    Contact::factory()->for($tenant1)->create();
    Contact::factory()->for($tenant2)->create();
    
    $superAdmin = User::factory()->create([
        'role' => 'super_admin',
        'tenant_id' => null,
    ]);
    
    actingAs($superAdmin);
    
    $contacts = Contact::withoutGlobalScope('tenant')->get();
    
    expect($contacts)->toHaveCount(2);
});
```

---

### 2. Authorization Testing

Test policies and permissions:

```php
test('admin can approve sender id', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->for($tenant)->create(['role' => 'tenant_admin']);
    $senderId = SenderId::factory()->for($tenant)->create(['status' => 'pending']);
    
    actingAs($admin);
    
    $this->assertTrue($admin->can('approve', $senderId));
});

test('regular user cannot approve sender id', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->for($tenant)->create(['role' => 'user']);
    $senderId = SenderId::factory()->for($tenant)->create(['status' => 'pending']);
    
    actingAs($user);
    
    $this->assertFalse($user->can('approve', $senderId));
});

test('user can only delete their own sender id', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->for($tenant)->create();
    $otherUser = User::factory()->for($tenant)->create();
    
    $userSenderId = SenderId::factory()->for($tenant)->create([
        'created_by_user_id' => $user->id
    ]);
    
    $otherSenderId = SenderId::factory()->for($tenant)->create([
        'created_by_user_id' => $otherUser->id
    ]);
    
    actingAs($user);
    
    expect($user->can('delete', $userSenderId))->toBeTrue()
        ->and($user->can('delete', $otherSenderId))->toBeFalse();
});
```

---

### 3. Queue Testing

Test that jobs are queued correctly:

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendBulkMessageJob;

test('message sending is queued', function () {
    Queue::fake();
    
    $tenant = Tenant::factory()->create(['sms_credits' => 100]);
    $contacts = Contact::factory()->for($tenant)->count(50)->create();
    
    $service = app(MessageService::class);
    $service->sendBulkMessage($tenant, $contacts, 'Test message');
    
    Queue::assertPushed(SendBulkMessageJob::class);
});

test('queue job processes messages correctly', function () {
    $tenant = Tenant::factory()->create(['sms_credits' => 100]);
    $contacts = Contact::factory()->for($tenant)->count(5)->create();
    
    $job = new SendBulkMessageJob($tenant, $contacts, 'Test');
    $job->handle();
    
    expect(MessageRecipient::count())->toBe(5);
});
```

---

### 4. Event Testing

Test that events are dispatched:

```php
use Illuminate\Support\Facades\Event;
use App\Events\MessageSent;

test('message sent event is dispatched', function () {
    Event::fake();
    
    $message = Message::factory()->create();
    
    $service = app(MessageService::class);
    $service->sendMessage($message);
    
    Event::assertDispatched(MessageSent::class, function ($event) use ($message) {
        return $event->message->id === $message->id;
    });
});
```

---

### 5. API Testing

Test external API integrations:

```php
use Illuminate\Support\Facades\Http;

test('twilio sms is sent correctly', function () {
    Http::fake([
        'api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201)
    ]);
    
    $provider = app(TwilioProvider::class);
    $result = $provider->send('+233201234567', 'Test message', 'YourApp');
    
    expect($result)->toBeTrue();
    
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/'.config('services.twilio.sid').'/Messages.json'
            && $request['To'] === '+233201234567'
            && $request['Body'] === 'Test message';
    });
});
```

---

## 🧪 Common Test Scenarios

### Testing Credit Deduction

```php
test('credits are deducted after sending message', function () {
    $tenant = Tenant::factory()->create(['sms_credits' => 100]);
    $contacts = Contact::factory()->for($tenant)->count(10)->create();
    
    $service = app(MessageService::class);
    $service->sendBulkMessage($tenant, $contacts, 'Test');
    
    expect($tenant->fresh()->sms_credits)->toBe(90);
});

test('credit transaction is recorded', function () {
    $tenant = Tenant::factory()->create(['sms_credits' => 100]);
    
    $service = app(CreditService::class);
    $service->deductCredits($tenant, 10, 'Message sending');
    
    $transaction = CreditTransaction::where('tenant_id', $tenant->id)->first();
    
    expect($transaction)->not->toBeNull()
        ->and($transaction->amount)->toBe(-10)
        ->and($transaction->balance_after)->toBe(90);
});
```

---

### Testing Scheduled Messages

```php
test('scheduled message is sent at correct time', function () {
    Queue::fake();
    
    $scheduledMessage = ScheduledMessage::factory()->create([
        'next_run_at' => now()->addMinute(),
        'is_active' => true,
    ]);
    
    // Run scheduler
    $this->artisan('schedule:run');
    
    Queue::assertNotPushed(SendScheduledMessageJob::class);
    
    // Travel forward in time
    $this->travel(2)->minutes();
    $this->artisan('schedule:run');
    
    Queue::assertPushed(SendScheduledMessageJob::class);
});
```

---

### Testing File Imports

```php
test('can import contacts from csv', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->for($tenant)->create();
    
    $csv = UploadedFile::fake()->createWithContent('contacts.csv', 
        "name,phone\nJohn Doe,+233201234567\nJane Doe,+233241234567"
    );
    
    actingAs($user);
    
    livewire(ImportContacts::class)
        ->set('file', $csv)
        ->call('import')
        ->assertHasNoErrors();
    
    expect(Contact::where('tenant_id', $tenant->id)->count())->toBe(2);
});
```

---

## 🔄 Continuous Integration

### GitHub Actions Example

`.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:14
        env:
          POSTGRES_DB: testing
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: postgres
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          extensions: mbstring, pdo, pdo_pgsql
      
      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist
      
      - name: Copy .env
        run: cp .env.example .env
      
      - name: Generate key
        run: php artisan key:generate
      
      - name: Run migrations
        run: php artisan migrate --force
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_PORT: 5432
          DB_DATABASE: testing
          DB_USERNAME: postgres
          DB_PASSWORD: postgres
      
      - name: Run tests
        run: php artisan test --coverage
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_PORT: 5432
          DB_DATABASE: testing
          DB_USERNAME: postgres
          DB_PASSWORD: postgres
```

---

## 📊 Coverage Reports

### Generate HTML Coverage Report

```bash
php artisan test --coverage --coverage-html=coverage
```

Open `coverage/index.html` in browser.

### Enforce Minimum Coverage

```bash
php artisan test --coverage --min=80
```

---

## ✅ Testing Checklist

Before merging code, ensure:

- [ ] All new features have tests
- [ ] Happy path is tested
- [ ] Error cases are tested
- [ ] Authorization is tested (who can/cannot do what)
- [ ] Tenant isolation is tested
- [ ] Validation rules are tested
- [ ] Edge cases are covered
- [ ] Tests are readable and well-named
- [ ] Test coverage meets minimum threshold (80%+)
- [ ] All tests pass in CI

---

**Remember**: Good tests are investments in code quality and developer confidence. Write tests that you'd want to read when debugging production issues at 3 AM!