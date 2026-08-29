<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin requests. This is
    | used to enable requests from different origins (like a frontend SPA on
    | a different subdomain) to communicate with your API.
    |
    */

    'paths' => ['api/v1/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5174')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Accept-Language',
        'Content-Type',
        'Authorization',
        'X-Request-Id',
        'Idempotency-Key',
        'X-XSRF-TOKEN',
    ],

    'exposed_headers' => [
        'X-Request-Id',
    ],

    'max_age' => 0,

    'supports_credentials' => true,

];
