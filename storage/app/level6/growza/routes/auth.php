<?php

use App\Http\Controllers\Web\Dashboard\DashboardController;
use App\Http\Controllers\Web\SecuritySettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authenticated Routes (LEVEL 3 / LEVEL 5)
|--------------------------------------------------------------------------
| Fortify registers the authentication endpoints themselves (login, logout,
| register, password reset, email verification, 2FA). This file holds the
| authenticated screens Growza owns.
|
| Every route here carries `active` so a suspended account is ejected on
| its very next request rather than when its session happens to expire.
*/

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    // Named 'dashboard' (not 'dashboard.index') so it matches every
    // existing route('dashboard') reference from LEVEL 3 (Fortify's
    // post-login redirect target, test assertions, view links) without
    // updating them — the URI '/dashboard' is what Fortify's
    // config('fortify.home') actually redirects to, so this route must
    // own that exact path.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'active', 'verified'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::get('/services', [DashboardController::class, 'services'])->name('services');
        Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');

        /*
         * /dashboard/orders/{order} is deliberately NOT registered yet.
         * There is no Order model to bind to until LEVEL 7 — a route
         * accepting an id with nothing real behind it would be exactly
         * the "fake dashboard where buttons do nothing" the project rules
         * prohibit. LEVEL 7 adds this route alongside the Order model and
         * OrderPolicy in the same change.
         */

        Route::get('/wallet', [DashboardController::class, 'wallet'])->name('wallet');
        Route::get('/transactions', [DashboardController::class, 'transactions'])->name('transactions');
        Route::get('/referrals', [DashboardController::class, 'referrals'])->name('referrals');
        Route::get('/support', [DashboardController::class, 'support'])->name('support');
        Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');
        Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    });

Route::middleware(['auth', 'active'])->group(function () {
    // Security settings: password change and two-factor management.
    // Not behind `verified` — a user who mistyped their email at signup
    // must still be able to secure their account. Lives outside the
    // /dashboard prefix (LEVEL 3 predates it) but is linked from the
    // dashboard's user menu and settings hub.
    Route::get('/settings/security', [SecuritySettingsController::class, 'show'])
        ->name('settings.security');
});
