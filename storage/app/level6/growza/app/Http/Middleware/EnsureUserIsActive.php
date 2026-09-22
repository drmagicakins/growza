<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks suspended and banned accounts from authenticated areas.
 *
 * The `users.status` column has existed since LEVEL 0 but nothing enforced
 * it until now. Without this, an admin suspending an account (LEVEL 16)
 * would have no effect on a user who is already logged in — the session
 * would keep working until it expired.
 *
 * The session is invalidated rather than merely redirected, so suspension
 * takes effect immediately rather than on next login.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isSuspended()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->status === 'banned'
                ? 'This account has been closed. Contact support if you believe this is an error.'
                : 'This account is currently suspended. Contact support to resolve it.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => [],
                ], 403);
            }

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
