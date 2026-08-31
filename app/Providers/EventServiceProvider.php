<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Domain event -> listener map. Kept centralized (rather than relying on
 * auto-discovery) so the event graph — e.g. OrderCompleted triggers both
 * a customer notification AND a referral-commission check — is
 * grep-able in one file instead of scattered across domains.
 */
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // \App\Domain\Orders\Events\OrderCompleted::class => [
        //     \App\Domain\Notifications\Listeners\SendOrderCompletedNotification::class,
        //     \App\Domain\Referrals\Listeners\CreditReferralCommission::class,
        // ],
    ];

    public function boot(): void
    {
        //
    }
}
