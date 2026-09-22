<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Domain\Catalogue\Models\Service;
use App\Domain\Reporting\Services\CustomerDashboardMetricsService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * All authenticated customer dashboard screens (LEVEL 5).
 *
 * Kept as one controller rather than nine near-identical single-action
 * classes: every method here does the same thing — resolve what data
 * exists today (which for orders/wallet/support/notifications is
 * genuinely nothing yet) and hand it to a view. There is no business
 * logic to separate into a Service beyond what CustomerDashboardMetricsService
 * already provides. Real per-domain complexity (placing an order, funding
 * a wallet) gets its own controller when that domain is built.
 */
class DashboardController extends Controller
{
    public function index(Request $request, CustomerDashboardMetricsService $metrics): View
    {
        return view('dashboard.index', [
            'metrics' => $metrics->forUser($request->user()),
        ]);
    }

    public function profile(): View
    {
        return view('dashboard.profile');
    }

    /**
     * Services are browsable while logged in, reading the same catalogue
     * tables the public services page reads (LEVEL 6) — but placing an
     * order is not possible until LEVEL 7, so every card says so plainly
     * instead of showing a button that does nothing.
     */
    public function services(): View
    {
        return view('dashboard.services', [
            'services' => Service::active()->ordered()->with(['platform', 'category'])->get(),
        ]);
    }

    public function orders(): View
    {
        return view('dashboard.orders');
    }

    public function wallet(): View
    {
        return view('dashboard.wallet');
    }

    public function transactions(): View
    {
        return view('dashboard.transactions');
    }

    public function referrals(Request $request): View
    {
        return view('dashboard.referrals', [
            'referredByCode' => $request->user()->referred_by_code,
        ]);
    }

    public function support(): View
    {
        return view('dashboard.support');
    }

    public function notifications(): View
    {
        return view('dashboard.notifications');
    }

    public function settings(): View
    {
        return view('dashboard.settings');
    }
}
