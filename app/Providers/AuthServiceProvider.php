<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Policy registrations. Populated at LEVEL 4 (RBAC) and extended by every
 * subsequent level that introduces a new authorizable model — per the
 * rule in ARCHITECTURE.md §7 that every state-changing controller action
 * must resolve to an explicit Policy check, never a role string compare.
 */
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Order::class => OrderPolicy::class,
        // SupportTicket::class => SupportTicketPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
