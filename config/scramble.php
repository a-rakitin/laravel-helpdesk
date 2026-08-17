<?php

return [
    'api_path' => 'api',

    'api_domain' => null,

    'info' => [
        'version' => env('API_VERSION', '1.0.0'),
        'description' => <<<'MARKDOWN'
# Laravel Helpdesk API

Portfolio project built with Laravel 12.

This API includes:

- authentication with Laravel Sanctum
- ticket management
- ticket comments
- notifications
- role-based access control
- policy-based authorization

Use the endpoints below to authenticate and work with the Helpdesk system.
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
