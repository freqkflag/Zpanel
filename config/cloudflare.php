<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Cloudflare API integration
    |
    */

    'api_token' => env('CLOUDFLARE_API_TOKEN'),

    'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),

    'api_endpoint' => env('CLOUDFLARE_API_ENDPOINT', 'https://api.cloudflare.com/client/v4'),

    'timeout' => env('CLOUDFLARE_TIMEOUT', 30),

    'retry_attempts' => env('CLOUDFLARE_RETRY_ATTEMPTS', 3),

    'retry_delay' => env('CLOUDFLARE_RETRY_DELAY', 1000),
];
