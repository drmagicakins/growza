<?php

/*
|--------------------------------------------------------------------------
| Authentication Configuration
|--------------------------------------------------------------------------
| LEVEL 3. This file did not exist before now, and its absence was the one
| thing that broke the entire auth stack.
|
| Laravel 11's slim skeleton ships no `config/auth.php`, so the framework
| silently falls back to its own baked-in defaults — which point at
| `App\Models\User`. This project deliberately keeps the model in a domain
| namespace (`App\Domain\Identity\Models\User`, per ARCHITECTURE.md §2), so
| every guarded request died with `Class "App\Models\User" not found`.
|
| Two other keys here matter and are not cosmetic:
|
|  - `provider` on the `web` guard is what Fortify resolves against
|    (`config('fortify.guard') === 'web'`), so the guard name must stay `web`
|    and its provider must stay `users`.
|  - the `users` password broker must use the same provider, or
|    `Features::resetPasswords()` sends reset links that can never validate.
|
| Sessions deliberately do NOT live here — see config/session.php. Growza
| separates cache/session/queue onto distinct Redis logical databases so a
| cache flush cannot drop live sessions (PROJECT_STATE.md, Security Decisions).
*/

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Sanctum-backed guard for the `/api/v1` surface (LEVEL 19 owns the
        // token scopes; the guard itself is registered here so the first API
        // consumer does not need to touch this file).
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            // Domain namespace, not `App\Models\User`. There is no model at
            // that path and there must not be — see the note above.
            'model' => App\Domain\Identity\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
     * How long Laravel remembers an authenticated session and how long it keeps
     * the "confirm your password again before this sensitive action" window.
     * Timing is declared here (rather than in the actions) so a policy change is
     * a config edit, not a code change.
     */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
