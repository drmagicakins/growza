<?php

return [
    'driver' => env('SESSION_DRIVER', 'redis'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => env('SESSION_ENCRYPT', false),
    'files' => storage_path('framework/sessions'),
    // Named Redis *store* connection, used only when SESSION_DRIVER=redis. It
    // names the Redis store bound to REDIS_SESSION_DB so a cache flush cannot
    // drop live sessions.
    'connection' => env('SESSION_CONNECTION'),
    // NOTE: the database driver resolves its own connection separately and must
    // fall back to the default one. A literal 'connection' => 'session' here
    // made every request throw "Database connection [session] not configured"
    // when SESSION_DRIVER=database, because no such DB connection exists.
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'growza_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
];
