<?php

namespace App\Domain\Reporting\DTOs;

/**
 * The six metrics LEVEL 5 requires on the customer dashboard.
 *
 * Money fields are integer minor units (kobo), matching the convention
 * fixed for the whole platform in DATABASE.md — never a float.
 */
final readonly class CustomerDashboardMetrics
{
    public function __construct(
        public int $walletBalanceMinor,
        public int $totalOrders,
        public int $activeOrders,
        public int $completedOrders,
        public int $totalSpentMinor,
        public int $referralEarningsMinor,
    ) {}

    /**
     * The honest state before wallets (LEVEL 8), orders (LEVEL 7) and
     * referral commissions (LEVEL 14) exist: every figure genuinely is
     * zero, because none of those things have happened yet. This is not
     * a placeholder standing in for fake data — it is what the metric
     * actually is right now.
     */
    public static function zero(): self
    {
        return new self(0, 0, 0, 0, 0, 0);
    }
}
