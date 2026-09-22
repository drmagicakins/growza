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
        // Authentication events (LEVEL 3)
        \Illuminate\Auth\Events\Login::class => [
            \App\Domain\Auth\Listeners\HandleSuccessfulLogin::class,
        ],
        \Illuminate\Auth\Events\Failed::class => [
            \App\Domain\Auth\Listeners\AuditFailedLogin::class,
        ],

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
