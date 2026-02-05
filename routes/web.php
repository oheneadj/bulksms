<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');



Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('messaging/send', \App\Livewire\Messaging\SendSms::class)->name('messaging.send');
    Route::get('messaging/templates', \App\Livewire\Messaging\Templates::class)->name('messaging.templates');
    Route::get('messaging/campaigns', \App\Livewire\Messaging\Campaigns::class)->name('messaging.campaigns');
    Route::get('messaging/campaign/{id}', \App\Livewire\Messaging\CampaignDetails::class)->name('messaging.campaign-details');
    Route::get('messaging/campaign/{campaign}/analytics', \App\Livewire\Messaging\CampaignAnalytics::class)->name('messaging.campaign-analytics');
    Route::get('messaging/history', \App\Livewire\MessageHistory::class)->name('messaging.history');
    Route::get('messaging/sender-ids', \App\Livewire\Messaging\SenderIds::class)->name('messaging.sender-ids');
    Route::get('messaging/birthdays', \App\Livewire\Birthdays::class)->name('messaging.birthdays');
    Route::get('/developer/api-keys', \App\Livewire\Messaging\ApiKeys::class)->name('developer.api-keys');
    Route::get('/developer/docs', function () {
        return view('developer.api-docs');
    })->name('developer.docs');
    Route::get('/developer/webhooks', \App\Livewire\Developer\Webhooks::class)->name('developer.webhooks');
    
    Route::get('contacts', \App\Livewire\Contacts::class)->name('contacts');
    Route::get('groups', \App\Livewire\Groups::class)->name('groups');
    Route::get('groups/{group}', \App\Livewire\GroupDetails::class)->name('groups.show');
    Route::get('billing', \App\Livewire\Billing::class)->name('billing');

    // Admin Routes
    Route::middleware(['can:super-admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/', '/admin/dashboard');
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/tenants', \App\Livewire\Admin\Tenants::class)->name('tenants');
        Route::get('/tenants/{tenant}/impersonate', [\App\Http\Controllers\Admin\TenantController::class, 'impersonate'])->name('tenants.impersonate');
        // Legacy routes removed as they are now handled by Livewire component actions
        
        // SMS Providers
        Route::get('/providers', [\App\Http\Controllers\Admin\ProviderController::class, 'index'])->name('providers.index');
        Route::post('/providers', [\App\Http\Controllers\Admin\ProviderController::class, 'store'])->name('providers.store');
        Route::post('/providers/sync-balances', [\App\Http\Controllers\Admin\ProviderController::class, 'syncBalances'])->name('providers.sync-balances');
        Route::post('/providers/{provider}/toggle', [\App\Http\Controllers\Admin\ProviderController::class, 'toggle'])->name('providers.toggle');
        Route::delete('/providers/{provider}', [\App\Http\Controllers\Admin\ProviderController::class, 'destroy'])->name('providers.destroy');
        
        // Sender ID Approvals
        Route::get('/sender-ids', [\App\Http\Controllers\Admin\SenderIdController::class, 'index'])->name('sender-ids');
        Route::post('/sender-ids/{senderId}/approve', [\App\Http\Controllers\Admin\SenderIdController::class, 'approve'])->name('sender-ids.approve');
        Route::post('/sender-ids/{senderId}/reject', [\App\Http\Controllers\Admin\SenderIdController::class, 'reject'])->name('sender-ids.reject');

        // Packages & Inventory
        Route::get('/packages', \App\Livewire\Admin\Packages\Index::class)->name('packages');
    });
});

Route::get('invitation/{token}', \App\Livewire\AcceptInvitation::class)->name('invitation.accept');

Route::middleware(['auth'])->group(function () {
    Route::match(['get', 'post'], 'billing/checkout', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('billing.checkout');
    Route::get('billing/success', [\App\Http\Controllers\PaymentController::class, 'success'])->name('billing.success');
    
    // Payment Callbacks
    Route::get('billing/callback/paystack', [\App\Http\Controllers\PaymentController::class, 'handlePaystackCallback'])->name('billing.callback.paystack');
    Route::get('billing/callback/flutterwave', [\App\Http\Controllers\PaymentController::class, 'handleFlutterwaveCallback'])->name('billing.callback.flutterwave');
    
    // Invoices
    Route::get('billing/invoice/{transaction}', [\App\Http\Controllers\PaymentController::class, 'downloadInvoice'])->name('billing.invoice');
});

Route::post('webhooks/stripe', [\App\Http\Controllers\PaymentController::class, 'webhook'])->name('webhooks.stripe');
Route::post('webhooks/paystack', [\App\Http\Controllers\PaymentController::class, 'handlePaystackWebhook'])->name('webhooks.paystack');
Route::post('webhooks/flutterwave', [\App\Http\Controllers\PaymentController::class, 'handleFlutterwaveWebhook'])->name('webhooks.flutterwave');

// SMS Delivery Reports
Route::any('webhooks/sms/twilio', [\App\Http\Controllers\WebhookController::class, 'handleTwilio'])->name('webhooks.sms.twilio');
Route::any('webhooks/sms/mnotify', [\App\Http\Controllers\WebhookController::class, 'handleMnotify'])->name('webhooks.sms.mnotify');
