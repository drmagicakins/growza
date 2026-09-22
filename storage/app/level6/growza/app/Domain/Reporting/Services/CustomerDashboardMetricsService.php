<?php

namespace App\Domain\Reporting\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Reporting\DTOs\CustomerDashboardMetrics;

/**
 * Computes the metrics shown on a customer's dashboard home (LEVEL 5).
 *
 * This is the seam LEVEL 5 was built against so that LEVEL 7 (orders),
 * LEVEL 8 (wallet) and LEVEL 14 (referrals) can fill in real queries here
 * without the dashboard view, controller, or route changing at all — the
 * same pattern used for marketing content in config/growza-marketing.php.
 *
 * Every query below is commented with the exact table/level that will
 * replace the zero it currently returns, so nothing here is a mystery
 * placeholder — it says precisely what it is standing in for.
 */
class CustomerDashboardMetricsService
{
    public function forUser(User $user): CustomerDashboardMetrics
    {
        return new CustomerDashboardMetrics(
            // LEVEL 8: $user->wallet?->balance ?? 0
            walletBalanceMinor: 0,

            // LEVEL 7: $user->orders()->count()
            totalOrders: 0,

            // LEVEL 7: $user->orders()->whereIn('status', ['queued', 'processing'])->count()
            activeOrders: 0,

            // LEVEL 7: $user->orders()->where('status', 'completed')->count()
            completedOrders: 0,

            // LEVEL 7/8: $user->orders()->where('status', '!=', 'refunded')->sum('amount_minor')
            totalSpentMinor: 0,

            // LEVEL 14: $user->referralCommissions()->where('status', 'paid')->sum('amount_minor')
            referralEarningsMinor: 0,
        );
    }
}
