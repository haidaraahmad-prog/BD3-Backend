<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Checkout.com (UAE / AED — sandbox + live)
    |--------------------------------------------------------------------------
    |
    | Sign up at https://dashboard.checkout.com — use sandbox keys for testing.
    | Test card: 4242 4242 4242 4242 · any future expiry · any CVV.
    |
    */

    'secret_key' => env('CHECKOUT_SECRET_KEY'),

    'public_key' => env('CHECKOUT_PUBLIC_KEY'),

    'webhook_secret' => env('CHECKOUT_WEBHOOK_SECRET'),

    'sandbox' => env('CHECKOUT_SANDBOX', true),

    'api_url' => env('CHECKOUT_SANDBOX', true)
        ? 'https://api.sandbox.checkout.com'
        : 'https://api.checkout.com',

    'currency' => env('CHECKOUT_CURRENCY', 'AED'),

    'processing_channel_id' => env('CHECKOUT_PROCESSING_CHANNEL_ID'),

];
