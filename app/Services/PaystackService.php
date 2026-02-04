<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected $baseUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('paystack.paymentUrl');
        $this->secretKey = config('paystack.secretKey');
        
        if (empty($this->secretKey)) {
             $this->secretKey = env('PAYSTACK_SECRET_KEY');
        }

        Log::info('PaystackService constructed', ['has_key' => !empty($this->secretKey)]);
    }

    /**
     * Initialize a transaction
     */
    public function initializeTransaction($email, $amount, $reference, $callbackUrl, $metadata = [])
    {
        // Paystack expects amount in cedis (multiply by 100)
        $amountInCedis = $amount * 100;

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $email,
                'amount' => $amountInCedis,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => $metadata,
                'currency' => 'GHS', // Defaulting to GHS for Paystack/Ghana context, or make dynamic
                'channels' => ['card', 'mobile_money']
            ]);

        if ($response->successful()) {
            /** @var array */
            return $response->json();
        }

        Log::error('Paystack Initialization Error: ' . $response->body());
        return null;
    }

    /**
     * Verify a transaction
     */
    public function verifyTransaction($reference)
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$reference}");

        if ($response->successful()) {
            /** @var array */
            return $response->json();
        }

        Log::error('Paystack Verification Error: ' . $response->body());
        return null;
    }
    /**
     * Verify Webhook Signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        return hash_equals($signature, hash_hmac('sha512', $payload, $this->secretKey));
    }
}
