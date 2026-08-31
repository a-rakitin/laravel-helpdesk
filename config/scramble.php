<?php

return [
    'api_path' => 'api',

    'api_domain' => null,

    'info' => [
        'version' => env('API_VERSION', '1.0.0'),
        'description' => <<<'MARKDOWN'
API for managing support tickets, comments, notifications, and user roles.

Use a Laravel Sanctum bearer token to access protected endpoints.
MARKDOWN,
    ],

    'servers' => null,

    'middleware' => [
        'web',
    ],

    'extensions' => [
        App\OpenApi\RateLimitResponseExtension::class,
    ],

    'ui' => [
        'title' => 'Laravel Helpdesk API Docs',
    ],
];
