<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'health', 'healthz.php', '*/health'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', '*'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

    /*
    |--------------------------------------------------------------------------
    | Vary Header
    |--------------------------------------------------------------------------
    |
    | When a server responds to a request with a specific origin in the
    | Access-Control-Allow-Origin header, the Vary: Origin header should be
    | included to indicate to caches that server responses may vary based on
    | the Origin of the request.
    |
    */
    'vary' => ['Origin'],

];
