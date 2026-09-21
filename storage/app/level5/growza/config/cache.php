<?php

return [
    'default' => env('CACHE_STORE', 'redis'),

    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
        'database' => [
            'driver' => 'database',
            'connection' => null,
            'table' => 'cache',
        ],
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'growza_cache_'),
];
