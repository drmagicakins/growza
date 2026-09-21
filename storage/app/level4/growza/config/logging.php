<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    'default' => env('LOG_CHANNEL', 'stack'),
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            // security/payments get their own channel so those events are
            // never mixed into general noise and can be shipped/alerted on
            // separately — see SECURITY.md and ARCHITECTURE.md §35.
            'channels' => ['single', 'security', 'payments'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'info',
            'days' => 90,
            'replace_placeholders' => true,
        ],

        'payments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payments.log'),
            'level' => 'info',
            'days' => 365,
            'replace_placeholders' => true,
        ],

        'providers' => [
            'driver' => 'daily',
            'path' => storage_path('logs/providers.log'),
            'level' => 'info',
            'days' => 30,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
    ],
];
