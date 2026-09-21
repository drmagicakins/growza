<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level permission gate, e.g. Route::middleware('permission:manage_refunds').
 * Implemented at LEVEL 4 against spatie/laravel-permission's `hasPermissionTo`.
 * Registered as an alias in bootstrap/app.php at LEVEL 0 so LEVEL 4 only has
 * to fill in the check, not also wire up middleware registration.
 */
class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless(
            $request->user()?->can($permission),
            403,
            'You do not have permission to perform this action.'
        );

        return $next($request);
    }
}
