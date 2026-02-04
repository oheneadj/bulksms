<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    protected $baseUrl;
    protected $secretKey;
    protected $secretHash;

    public function __construct()
    {
        $this->baseUrl = config('flutterwave.paymentUrl');
        $this->secretKey = config('flutterwave.secretKey');
        $this->secretHash = config('flutterwave.secretHash');
    }

    /**
     * Initialize a payment
     */
    public function initializePayment($customer, $amount, $txRef, $redirectUrl, $meta = [])
    {
        $payload = [
            'tx_ref' => $txRef,
            'amount' => $amount,
            'currency' => 'GHS', // Default to GHS or make dynamic
            'redirect_url' => $redirectUrl,
            'payment_options' => 'card,mobilemoneyghana,mobilemoneyuganda,mobilemoneyzambia',
            'customer' => [
                'email' => $customer['email'],
                'name' => $customer['name'],
                'phonenumber' => $customer['phone'] ?? null,
            ],
            'meta' => $meta,
            'customizations' => [
                'title' => 'SMS Credit Top-up',
                'description' => 'Purchase of SMS credits',
                'logo' => asset('logo.png'), // Ensure a logo exists or use a remote URL
            ]
        ];

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/payments", $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Flutterwave Initialization Error: ' . $response->body());
        return null;
    }

    /**
     * Verify a transaction by ID
     */
    public function verifyTransaction($transactionId)
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/{$transactionId}/verify");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Flutterwave Verification Error: ' . $response->body());
        return null;
    }
    /**
     * Verify Webhook Signature
     */
    public function verifyWebhookSignature($signature)
    {
        return $signature && $signature === $this->secretHash;
    }
}
