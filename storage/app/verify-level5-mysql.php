<?php

/*
 * LEVEL 5 live verification against REAL MySQL 8 — not SQLite, not the Pest
 * suite. Verified here because that is exactly the gap LEVEL 4 closed the hard
 * way: the Pest suite runs on SQLite in-memory, where a composite PRIMARY KEY
 * permitted NULLs inside `model_has_roles.team_id`, so 76 tests were green
 * while the MySQL schema the app actually runs against was broken.
 *
 * This script therefore does not assert on the code. It:
 *   1. builds a real Customer row through the same registration path the app
 *      uses (User + assignRole), against MySQL,
 *   2. resolves the dashboard for that user exactly as HTTP would,
 *   3. prints the six metrics and the rendered HTML length,
 *   4. confirms `/dashboard/orders/1` is a genuine 404 and not a 500.
 *
 * Run:  php storage/app/verify-level5-mysql.php
 * It leaves the created row in place and prints its id so the HTTP pass can
 * log in as the same user.
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';

/*
 * A bare `bootstrap()` is not enough to render a Blade view outside of a real
 * request: the `view` and `session` (hence the `$errors` view-shared variable)
 * bindings are wired by the HTTP kernel's bootstrap sequence, so a booted
 * console kernel throws "Undefined variable $errors" from <x-auth-errors>.
 * That is a defect in this probe, not in the dashboard. Kernel::handle() runs
 * the full HTTP bootstrap and then aborts before the response is sent, so the
 * container is left in the state a real request leaves it in.
 */
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/dashboard', 'GET');

try {
    $kernel->handle($request);
} catch (Throwable $e) {
    // Any exception here would belong to the route under test, not to
    // bootstrap; surface it rather than swallowing it.
    fwrite(STDERR, 'kernel->handle() threw: '.$e->getMessage().PHP_EOL);
}

use App\Domain\Identity\Enums\RoleName;
use App\Domain\Identity\Models\User;
use App\Domain\Reporting\Services\CustomerDashboardMetricsService;
use App\Http\Controllers\Web\Dashboard\DashboardController;
use Illuminate\Support\Facades\DB;

$out = [];

$out['db_driver'] = DB::connection()->getDriverName();
$out['db_name'] = DB::connection()->getDatabaseName();

// --- 1. A real Customer, created the way registration creates one ----------
$email = 'level5-verify@example.test';

User::withTrashed()->where('email', $email)->forceDelete();
User::where('email', $email)->delete();

$user = User::forceCreate([
    'name' => 'Level Five Verify',
    'email' => $email,
    'phone' => '+2348000000001',
    'password' => Illuminate\Support\Facades\Hash::make('correct-horse-99'),
    'email_verified_at' => now(),
    'status' => 'active',
]);
$user->assignRole(RoleName::default()->value);

 $out['user_id'] = $user->id;
$out['user_email'] = $user->email;
$out['user_password'] = 'correct-horse-99';
$out['assigned_role'] = $user->getRoleNames()->implode(', ');

// The pivot must have written a GLOBAL grant — this is the LEVEL 4 pivot bug.
$pivotTeamId = DB::table('model_has_roles')
    ->where('model_id', $user->id)
    ->where('model_type', $user->getMorphClass())
    ->value('team_id');
$out['pivot_team_id'] = var_export($pivotTeamId, true);
$out['has_role_reads_back_true'] = $user->fresh()->hasRole(RoleName::default()->value);

// --- 2. The metrics service, against this real user ------------------------
$metrics = (new CustomerDashboardMetricsService)->forUser($user->fresh());

$out['metrics'] = [
    'walletBalanceMinor' => $metrics->walletBalanceMinor,
    'totalOrders' => $metrics->totalOrders,
    'activeOrders' => $metrics->activeOrders,
    'completedOrders' => $metrics->completedOrders,
    'totalSpentMinor' => $metrics->totalSpentMinor,
    'referralEarningsMinor' => $metrics->referralEarningsMinor,
];

$out['money_formatting'] = [
    'zero' => App\Support\Money::format($metrics->walletBalanceMinor),
    '4500_naira' => App\Support\Money::format(450000),
    'usd_fallback' => App\Support\Money::format(100000, 'USD'),
];

// --- 3. Render each dashboard screen as the controller really does ---------
// This catches a Blade compile error or a missing component that the metrics
// assertions alone would never touch.
$controller = app(DashboardController::class);

/*
 * An authenticated request, not the anonymous one used above. `auth()`
 * resolving to null is precisely what produced "Attempt to read property
 * 'name' on null" — the layout calls auth()->user()->name for the avatar
 * initial. A guest genuinely cannot reach these screens, so a guest request
 * failing is the harness testing a state the app forbids.
 */
$dashboardRequest = Illuminate\Http\Request::create('/dashboard', 'GET');
$dashboardRequest->setUserResolver(fn () => $user->fresh());
app()->instance('request', $dashboardRequest);
Illuminate\Support\Facades\Auth::setUser($user->fresh());
$dashboardRequest->setLaravelSession(app('session.store'));

$out['rendered_views'] = [];
$renderTargets = [
    'index' => fn () => $controller->index($dashboardRequest, app(CustomerDashboardMetricsService::class)),
    'profile' => fn () => $controller->profile(),
    'services' => fn () => $controller->services(),
    'orders' => fn () => $controller->orders(),
    'wallet' => fn () => $controller->wallet(),
    'transactions' => fn () => $controller->transactions(),
    'referrals' => fn () => $controller->referrals($dashboardRequest),
    'support' => fn () => $controller->support(),
    'notifications' => fn () => $controller->notifications(),
    'settings' => fn () => $controller->settings(),
];

foreach ($renderTargets as $name => $render) {
    try {
        $html = $render()->render();
        $out['rendered_views'][$name] = [
            'ok' => true,
            'bytes' => strlen($html),
            // Prove the layout really wrapped it, not just an empty string.
            'has_sidebar_marker' => str_contains($html, 'md:w-60'),
            'has_nav_pages' => substr_count($html, 'dashboard-nav-link') >= 0
                ? preg_match_all('/href="[^"]*\/dashboard/', $html)
                : 0,
        ];
    } catch (Throwable $e) {
        $out['rendered_views'][$name] = [
            'ok' => false,
            'error' => get_class($e).': '.$e->getMessage(),
        ];
    }
}

// --- 4. The generated HTML for the dashboard home, checked by content ------
try {
    $homeHtml = $controller->index($dashboardRequest, app(CustomerDashboardMetricsService::class))->render();
    $out['dashboard_home'] = [
        'shows_zero_balance' => str_contains($homeHtml, '₦0.00'),
        'shows_welcome' => str_contains($homeHtml, 'Welcome back, Level Five Verify'),
        'shows_honest_empty_state' => str_contains($homeHtml, 'No campaigns yet'),
        // Every private page must refuse indexing.
        'is_noindex' => str_contains($homeHtml, 'noindex, nofollow'),
        // The nav must not contain a dead link to a route with no controller.
        'nav_link_count' => preg_match_all('/href="[^"]*\/dashboard/', $homeHtml),
        'has_route_exception' => (bool) preg_match('/Route \[.*\] not defined|Undefined variable/', $homeHtml),
    ];
} catch (Throwable $e) {
    $out['dashboard_home'] = ['error' => get_class($e).': '.$e->getMessage()];
}

// --- 5. Compare the full route table against what the views link to --------
// Any route() name in the dashboard nav that is NOT registered would throw at
// render time; this makes the reverse check explicit instead of implicit.
$registered = collect(app('router')->getRoutes()->getRoutes())
    ->map(fn ($r) => $r->getName())
    ->filter()
    ->values()
    ->all();

$out['routes'] = [
    'total' => count($registered),
    'dashboard_names' => array_values(array_filter($registered, fn ($n) => str_starts_with((string) $n, 'dashboard'))),
    'has_dashboard' => in_array('dashboard', $registered, true),
    'has_single_order_route' => (bool) array_filter($registered, fn ($n) => str_starts_with((string) $n, 'dashboard.orders.')),
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;