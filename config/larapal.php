<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayPal Environment
    |--------------------------------------------------------------------------
    |
    | This value switch PayPal environment between Live and Sandbox
    |
    | Supported: "sandbox", "live"
    |
    */
    'mode' => env('PAYPAL_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Credentials
    |--------------------------------------------------------------------------
    |
    | With this values the package will connect with PayPal and will do magic
    | things (like Subscriptions). You can obtain this values, making an App
    | in developer.paypal.com portal
    |
    */
    'credentials' => [
        'sandbox' => [
            'client_id' => env('PAYPAL_SANDBOX_ID'),
            'client_secret' => env('PAYPAL_SANDBOX_SECRET')
        ],
        'production' => [
            'client_id' => env('PAYPAL_PRODUCTION_ID'),
            'client_secret' => env('PAYPAL_PRODUCTION_SECRET')
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Currency
    |--------------------------------------------------------------------------
    |
    | Transaction will be made with this currency if not overloaded
    |
    */
    'currency' => env('PAYPAL_CURRENCY', 'EUR'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Locale
    |--------------------------------------------------------------------------
    |
    | Change language of PayPal interface
    |
    */
    'locale' => env('PAYPAL_LOCALE', 'en-US'),

    /*
    |--------------------------------------------------------------------------
    | PayPal URL: return
    |--------------------------------------------------------------------------
    |
    | User will be redirected here after successful payment
    |
    */
    'return_url' => '/',

    /*
    |--------------------------------------------------------------------------
    | PayPal URL: cancel
    |--------------------------------------------------------------------------
    |
    | User will be redirected here if him cancel payment
    |
    */
    'cancel_url' => '/',
];
