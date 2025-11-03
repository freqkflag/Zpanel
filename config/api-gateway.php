<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kong API Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Kong API Gateway integration
    |
    */

    'kong_admin_url' => env('KONG_ADMIN_URL', 'http://kong:8001'),

    'kong_proxy_url' => env('KONG_PROXY_URL', 'http://kong:8000'),

    'default_rate_limit' => env('KONG_DEFAULT_RATE_LIMIT', 1000),

    'rate_limit_window' => env('KONG_RATE_LIMIT_WINDOW', 3600), // 1 hour in seconds
];
