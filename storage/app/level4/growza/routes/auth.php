<?php

use App\Http\Controllers\Web\SecuritySettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authenticated Routes (LEVEL 3)
|--------------------------------------------------------------------------
| Fortify registers the authentication endpoints themselves (login, logout,
| register, password reset, email verification, 2FA). This file holds the
| authenticated screens Growza owns.
|
| Every route here carries `active` so a suspended account is ejected on
| its very next request rather than when its session happens to expire.
*/

Route::middleware(['auth', 'active'])->group(function () {

    /*
     * Placeholder landing page. Fortify redirects here after login, so a
     * route must exist or every successful login lands on a 404.
     *
     * LEVEL 5 replaces this with the real customer dashboard (metrics,
     * wallet balance, order summaries). It is intentionally thin rather
     * than a fake dashboard with non-functional widgets — see the
     * project's "no fake implementations" rule.
     */
    Route::view('/dashboard', 'dashboard.placeholder')
        ->middleware('verified')
        ->name('dashboard');

    // Security settings: password change and two-factor management.
    // Not behind `verified` — a user who mistyped their email at signup
    // must still be able to secure their account.
    Route::get('/settings/security', [SecuritySettingsController::class, 'show'])
        ->name('settings.security');
});
