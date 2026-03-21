<?php
// Built by Louis Innovations (www.louis-innovations.com)

return [

    /*
    |--------------------------------------------------------------------------
    | SADAD Merchant Credentials
    |--------------------------------------------------------------------------
    |
    | Your SADAD merchant ID (7 digits) and secret key. These are required
    | for all API calls and checkout signature generation.
    |
    */

    'merchant_id' => env('SADAD_MERCHANT_ID', ''),

    'secret_key' => env('SADAD_SECRET_KEY', ''),

    'website' => env('SADAD_WEBSITE', ''),

    /*
    |--------------------------------------------------------------------------
    | SADAD Environment
    |--------------------------------------------------------------------------
    |
    | Controls whether the SDK communicates with the SADAD test or live
    | environment. Accepted values: "test" | "live".
    |
    */

    'environment' => env('SADAD_ENVIRONMENT', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Checkout Mode
    |--------------------------------------------------------------------------
    |
    | The default checkout version to use when calling Sadad::checkout().
    | Accepted values: "v1.1" | "v2.1" | "v2.2".
    |
    */

    'checkout_mode' => env('SADAD_CHECKOUT_MODE', 'v1.1'),

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | Language for SADAD payment pages and API responses.
    | Accepted values: "eng" | "arb".
    |
    */

    'language' => env('SADAD_LANGUAGE', 'eng'),

    /*
    |--------------------------------------------------------------------------
    | Callback & Webhook URLs
    |--------------------------------------------------------------------------
    |
    | SADAD will POST payment results to these URLs. The package registers
    | built-in routes at /sadad/callback and /sadad/webhook automatically,
    | so you can set these to your app domain + those paths, or override with
    | custom URLs.
    |
    */

    'callback_url' => env('SADAD_CALLBACK_URL', null),

    'webhook_url' => env('SADAD_WEBHOOK_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Order Number Prefix
    |--------------------------------------------------------------------------
    |
    | Optional prefix to prepend to order IDs sent to SADAD.
    | Example: "ORD-" will produce "ORD-12345".
    |
    */

    'order_prefix' => env('SADAD_ORDER_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Redirect URLs (after callback)
    |--------------------------------------------------------------------------
    |
    | After the built-in SadadCallbackController processes a payment result,
    | the user is redirected to one of these routes/URLs.
    |
    */

    'redirect' => [
        'success' => env('SADAD_REDIRECT_SUCCESS', '/'),
        'failure' => env('SADAD_REDIRECT_FAILURE', '/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, all SADAD API requests, webhook payloads, and callback
    | data are written to the Laravel log using the specified channel.
    |
    */

    'logging' => env('SADAD_LOGGING', false),

    'log_channel' => env('SADAD_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When true, raw API request and response bodies are included in log
    | entries. Never enable in production.
    |
    */

    'debug' => env('SADAD_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Transaction Logging Table
    |--------------------------------------------------------------------------
    |
    | The package ships an optional migration that creates a sadad_transactions
    | table. Set this to true to enable automatic logging of every webhook and
    | callback event to that table.
    |
    */

    'log_transactions' => env('SADAD_LOG_TRANSACTIONS', false),

];
