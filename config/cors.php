<?php

return [
    'paths' => [
        'api/*',
        'oauth/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter([
        env('FRONTEND_URL'),
        env('APP_URL'),
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://front.complex-solutions.uz',
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-Pagination-Current-Page',
        'X-Pagination-Last-Page',
        'X-Pagination-Per-Page',
        'X-Pagination-Total',
    ],

    'max_age' => 0,

    'supports_credentials' => false,
];
