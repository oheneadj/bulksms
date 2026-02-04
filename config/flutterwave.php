<?php

return [
    'publicKey' => env('FLW_PUBLIC_KEY'),
    'secretKey' => env('FLW_SECRET_KEY'),
    'secretHash' => env('FLW_SECRET_HASH'),
    'paymentUrl' => env('FLW_PAYMENT_URL', 'https://api.flutterwave.com/v3'),
];
