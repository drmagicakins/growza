<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin routes are registered separately from `web` so they can
            // carry their own middleware group (auth + verified + permission
            // gate) without leaking into the public/customer route group.
            // See ARCHITECTURE.md §15.
            Illuminate\Support\Facades\Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        $middleware->alias([
            // Registered here at LEVEL 0; implemented as their owning
            // levels land (RBAC permission gate at LEVEL 4, API key
            // scoping at LEVEL 19, etc). See PROJECT_STATE.md for status.
            'permission' => App\Http\Middleware\EnsureUserHasPermission::class,
            'role' => App\Http\Middleware\EnsureUserHasRole::class,
            'api.scope' => App\Http\Middleware\EnsureApiTokenHasScope::class,
            // Suspension enforcement (LEVEL 3). Applied to every
            // authenticated route group so an admin suspension takes effect
            // on the suspended user's very next request rather than whenever
            // their session happens to expire.
            'active' => App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom rendering for branded error pages (LEVEL 27) and safe
        // API error envelopes (§52) is added at LEVEL 27, not LEVEL 0 —
        // left as framework defaults until that level is implemented.
    })
    ->create();
