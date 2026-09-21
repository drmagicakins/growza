<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks the current Sanctum token's abilities against the scope required
 * by the route (LEVEL 19 / LEVEL 20 — reseller API keys). A session-based
 * (non-token) request is allowed through unchanged, since ability checks
 * only make sense for token auth.
 */
class EnsureApiTokenHasScope
{
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && method_exists($token, 'can')) {
            abort_unless($token->can($scope), 403, 'This API token does not have the required scope.');
        }

        return $next($request);
    }
}
