<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct(
        protected \App\Services\PaystackService $paystackService,
        protected \App\Services\FlutterwaveService $flutterwaveService
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Checkout Session.
     */
    public function checkout(Request $request)
    {
        Log::info('Checkout Request', [
            'mode' => $request->input('mode'),
            'type' => $request->input('type'), 
            'wantsJson' => $request->wantsJson(),
            'ajax' => $request->ajax(),
        ]);

        $request->validate([
            'gateway' => 'required|in:stripe,paystack,flutterwave',
            'package_id' => 'required_without:type|exists:credit_packages,id',
            'type' => 'nullable|in:credit,sender_id',
            'id' => 'required_if:type,sender_id', // ID of the sender_id record
        ]);
        
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $type = $request->input('type', 'credit');
        $amount = 0;
        $description = '';
        $metadata = [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'payment_type' => $type, 
        ];

        if ($type === 'sender_id') {
            $senderIdRecord = \App\Models\SenderId::where('user_id', $user->id)->findOrFail($request->id);
            
            if ($senderIdRecord->status !== 'payment_pending') {
                return back()->with('error', 'This Sender ID is not ready for payment.');
            }

            // Fixed price for Sender ID from config
            $amount = config('bulksms.sender_id_price', 50.00); 
            $description = "Sender ID Activation: " . $senderIdRecord->sender_id;
            $metadata['sender_id_record'] = $senderIdRecord->id;
            
            // Pass a dummy package-like object or array if needed by downstream, 
            // but better to pass raw amount/desc to checkout methods.
            // We'll adapt downstream methods to accept ($amount, $description, $metadata) instead of $package.
        } else {
            $package = \App\Models\CreditPackage::findOrFail($request->package_id);
            
             // Inventory Check
            $inventory = \App\Models\SystemCredit::first();
            if ($inventory && $inventory->balance < $package->credits) {
                 if ($inventory) {
                      $admins = \App\Models\User::where('role', 'super_admin')->get();
                     \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SystemCreditLowNotification($inventory->balance));
                 }
                 
                 if (request()->wantsJson() || request('mode') === 'inline') {
                     return response()->json(['error' => 'This package is currently unavailable.'], 400);
                 }
                 return back()->with('error', 'This package is currently unavailable.');
            }
            
            $amount = $package->price;
            $description = "Purchase of {$package->credits} SMS credits";
            $metadata['amount_credits'] = $package->credits;
            $metadata['package_id'] = $package->id;
        }

        $gateway = $request->gateway;

        // Normalize arguments for checkout methods: User, Amount, Description, Metadata
        switch ($gateway) {
            case 'paystack':
                return $this->paystackCheckoutCommon($user, $amount, $description, $metadata);
            case 'flutterwave':
                return $this->flutterwaveCheckoutCommon($user, $amount, $description, $metadata);
            case 'stripe':
            default:
                return $this->stripeCheckoutCommon($user, $amount, $description, $metadata);
        }
    }

    protected function stripeCheckoutCommon($user, $amount, $description, $metadata)
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Payment',
                        'description' => $description,
                    ],
                    'unit_amount' => (int)($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}&gateway=stripe',
            'cancel_url' => route('billing'),
            'metadata' => $metadata,
        ]);

        return redirect($session->url);
    }

    protected function paystackCheckoutCommon($user, $amount, $description, $metadata)
    {
        $reference = 'PAYSTACK-' . Str::uuid();
        $callbackUrl = route('billing.callback.paystack');
        
        // Let's call initialize to get the access_code and reference, it's safer.
        try {
            Log::info('Calling Paystack Service Init', ['email' => $user->email, 'amount' => $amount]);
            
            $data = $this->paystackService->initializeTransaction(
                $user->email,
                $amount, 
                $reference,
                $callbackUrl,
                $metadata
            );
        } catch (\Exception $e) {
            Log::error('Paystack Controller Exception: ' . $e->getMessage());
            if (request()->wantsJson() || request('mode') === 'inline') {
                return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Server Error during payment initialization.');
        }

         Log::info('Paystack Init Result', ['data' => $data]);

        if ($data && isset($data['data']['access_code'])) {
            if (request()->wantsJson() || request('mode') === 'inline') {
                return response()->json([
                    'key' => config('paystack.publicKey'),
                    'email' => $user->email,
                    'amount' => $amount * 100, // Kobo
                    'currency' => 'GHS',
                    'ref' => $reference,
                    //'access_code' => $data['data']['access_code'], // If using access_code, authentication is bypassed on frontend? 
                    // Paystack Inline documentation says you can use key + email + amount OR key + access_code.
                    // Using access code is often cleaner.
                    'access_code' => $data['data']['access_code'],
                    'callback_url' => $callbackUrl 
                ]);
            }
            
            // Fallback to standard redirect if not inline request (legacy support)
            if (isset($data['data']['authorization_url'])) {
                return redirect($data['data']['authorization_url']);
            }
        }

        if (request()->wantsJson() || request('mode') === 'inline') {
            return response()->json(['error' => 'Failed to initialize Paystack payment. Please check API keys.'], 400);
        }

        return back()->with('error', 'Failed to initialize Paystack payment.');
    }

    protected function flutterwaveCheckoutCommon($user, $amount, $description, $metadata)
    {
        $txRef = 'FLW-' . Str::uuid();
        $redirectUrl = route('billing.callback.flutterwave');

        $customer = [
            'email' => $user->email,
            'name' => $user->name,
            'phone' => '0540000000', // Should be user's phone
        ];

        $data = $this->flutterwaveService->initializePayment($customer, $amount, $txRef, $redirectUrl, $metadata);

        if ($data && isset($data['data']['link'])) {
            return redirect($data['data']['link']);
        }

        return back()->with('error', 'Failed to initialize Flutterwave payment.');
    }

    public function handlePaystackCallback(Request $request)
    {
        $reference = $request->query('reference');
        if (!$reference) {
            return redirect()->route('billing')->with('error', 'No payment reference provided.');
        }

        $response = $this->paystackService->verifyTransaction($reference);

        if ($response && $response['status'] && $response['data']['status'] === 'success') {
            $data = $response['data'];
            $metadata = $data['metadata'] ?? [];

            if ($this->handlePaymentSuccess($data, $metadata, $reference, 'paystack', $data['amount'] / 100)) {
                 $type = $metadata['payment_type'] ?? 'credit';
                 $route = $type === 'sender_id' ? 'messaging.sender-ids' : 'billing';
                 return redirect()->route($route)->with('success', 'Payment successful!');
            }
        }

        return redirect()->route('billing')->with('error', 'Payment verification failed.');
    }

    public function handleFlutterwaveCallback(Request $request)
    {
        $status = $request->query('status');
        $txRef = $request->query('tx_ref');
        $transactionId = $request->query('transaction_id');

        if ($status !== 'successful' || !$transactionId) {
             return redirect()->route('billing')->with('error', 'Payment was not successful.');
        }

        $response = $this->flutterwaveService->verifyTransaction($transactionId);

        if ($response && $response['status'] === 'success' && $response['data']['status'] === 'successful') {
            $data = $response['data'];
            $meta = $data['meta'] ?? [];
            
            if ($this->handlePaymentSuccess($data, $meta, $txRef, 'flutterwave', $data['amount'])) {
                $type = $meta['payment_type'] ?? 'credit';
                 $route = $type === 'sender_id' ? 'messaging.sender-ids' : 'billing';
                 return redirect()->route($route)->with('success', 'Payment successful!');
            }
        }

        return redirect()->route('billing')->with('error', 'Payment verification failed.');
    }

    // Existing Stripe success (modified to just redirect or handle simple success view)
    public function success(Request $request)
    {
        return redirect()->route('billing')->with('success', 'Stripe Payment successful! Credits will be updated momentarily via webhook.');
    }

    // Generic/Flexible Callback Handler
    protected function handlePaymentSuccess($data, $metadata, $reference, $gateway, $amountPaid)
    {
        $userId = $metadata['user_id'] ?? null;
        $tenantId = $metadata['tenant_id'] ?? null;
        $type = $metadata['payment_type'] ?? 'credit'; // Default to credit for existing flows

        if ($type === 'sender_id') {
             $senderIdRecordId = $metadata['sender_id_record'] ?? null;
             return $this->processSenderIdActivation($userId, $tenantId, $senderIdRecordId, $reference, $gateway, $amountPaid);
        }

        // Default: Credit Top Up
        $credits = $metadata['amount_credits'] ?? 0;
        $packageId = $metadata['package_id'] ?? null;
        
        return $this->recordTransaction($userId, $tenantId, $credits, $reference, $gateway, $amountPaid, $packageId);
    }
    
    protected function processSenderIdActivation($userId, $tenantId, $senderIdRecordId, $reference, $gateway, $amountPaid)
    {
         if (Transaction::query()->where('reference', $reference)->exists()) {
             return true;
         }

        $senderId = \App\Models\SenderId::find($senderIdRecordId);
        if ($senderId) {
            $senderId->update(['status' => 'active']); // Or 'approved' if active == approved in your enum
             // Let's assume 'approved' is the final state after payment if 'payment_pending' was distinct?
             // Or 'active' if you added that state. Let's send 'active'.
        }

        Transaction::create([
            'user_id' => $userId,
            'type' => 'purchase', // Different from 'deposit'
            'amount' => 0, // No credits added
            'description' => "Sender ID Activation: " . ($senderId->sender_id ?? 'Unknown') . " via " . ucfirst($gateway),
            'reference' => $reference,
            'balance_after' => Tenant::find($tenantId)->sms_credits ?? 0,
        ]);
        
        return true;
    }

    // Consolidated helper (Legacy/Credit specific now)
    protected function recordTransaction($userId, $tenantId, $credits, $reference, $gateway, $amountPaid, $packageId = null)
    {
        $tenant = Tenant::query()->find($tenantId);
        if (!$tenant || !$userId) return false;

        // Verify Amount integrity if package ID is present
        if ($packageId) {
             $package = \App\Models\CreditPackage::find($packageId);
             if ($package) {
                  if ($amountPaid < ($package->price - 0.01)) {
                      Log::critical("Potential Payment Fraud Detected! User {$userId} tried to pay {$amountPaid}. Ref: {$reference}");
                      return false;
                  }
             }
        }
        
        // Prevent duplicate processing
        if (Transaction::query()->where('reference', $reference)->exists()) {
             return true;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($tenant, $userId, $credits, $reference, $gateway, $amountPaid) {
            $tenant->increment('sms_credits', $credits);

            // Deduct system inventory
            $inventory = \App\Models\SystemCredit::first();
            if ($inventory) {
                $inventory->decrement('balance', $credits);
                $inventory->increment('total_sold', $credits);

                if ($inventory->refresh()->balance < 100000) {
                    $admins = \App\Models\User::where('role', 'super_admin')->get();
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SystemCreditLowNotification($inventory->balance));
                }
            }

            Transaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $credits,
                // Append amount paid to description for audit trail
                'description' => "Credit Top-up via " . ucfirst($gateway) . " (" . number_format($amountPaid, 2) . ")",
                'reference' => $reference,
                'balance_after' => $tenant->sms_credits,
            ]);
        });
        
        return true;
    }

    // Keep Stripe Webhook as is for now
    public function webhook(Request $request)
    {
        // ... (Existing Stripe Webhook logic)
        $endpoint_secret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->fulfillOrder($session);
        }

        return response()->json(['status' => 'success']);
    }

    protected function fulfillOrder($session)
    {
        $tenantId = $session->metadata->tenant_id;
        $userId = $session->metadata->user_id;
        $credits = $session->metadata->amount_credits;

        // Use the common record method or keep separate if Stripe data is vastly different
        // Re-using logic:
        $this->recordTransaction($userId, $tenantId, $credits, 'STRIPE-' . $session->id, 'stripe', $session->amount_total / 100);
    }
    public function handlePaystackWebhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (!$this->paystackService->verifyWebhookSignature($payload, $signature)) {
             return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        
        if ($event['event'] === 'charge.success') {
            $data = $event['data'];
            $metadata = $data['metadata'] ?? [];
            
            $reference = $data['reference'];
            $amountPaid = $data['amount'] / 100;

            if ($this->handlePaymentSuccess($data, $metadata, $reference, 'paystack', $amountPaid)) {
                return response()->json(['status' => 'success']);
            }
        }

        return response()->json(['status' => 'ignored']);
    }

    public function handleFlutterwaveWebhook(Request $request)
    {
        $signature = $request->header('verif-hash');
        
        if (!$this->flutterwaveService->verifyWebhookSignature($signature)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        $payload = $request->getContent();
        $event = json_decode($payload, true);

        if (isset($event['event']) && $event['event'] === 'charge.completed' && $event['data']['status'] === 'successful') {
            $data = $event['data'];
            $meta = $data['meta'] ?? []; // Flutterwave webhooks might flatten meta or keep it nested; assuming standard structure
            
            // If meta is missing from webhook, we verify via transaction ID.
            if (empty($meta['user_id'])) {
                // If meta is missing in webhook, verify via API
                $verification = $this->flutterwaveService->verifyTransaction($data['id']);
                if ($verification && $verification['status'] === 'success') {
                    $meta = $verification['data']['meta'] ?? [];
                }
            }

            if ($this->handlePaymentSuccess($data, $meta, $data['tx_ref'], 'flutterwave', $data['amount'])) {
                 return response()->json(['status' => 'success']);
            }
        }

        return response()->json(['status' => 'ignored']);
    }
    public function downloadInvoice(Transaction $transaction)
    {
        // Authorization
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        return view('billing.invoice', compact('transaction'));
    }
}
