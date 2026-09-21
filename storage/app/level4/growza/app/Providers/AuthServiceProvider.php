<?php

namespace App\Providers;

use App\Domain\Identity\Enums\RoleName;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Policy registrations. Populated at LEVEL 4 (RBAC) and extended by every
 * subsequent level that introduces a new authorizable model — per the
 * rule in ARCHITECTURE.md §7 that every state-changing controller action
 * must resolve to an explicit Policy check, never a role string compare.
 */
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        // Order::class => OrderPolicy::class,
        // SupportTicket::class => SupportTicketPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        /*
         * Super Admin bypasses every ability check. Returning null (not
         * false) for non-Super-Admins lets the request fall through to the
         * normal Policy/permission check instead of being decided here —
         * only a Super Admin short-circuits to an explicit `true`.
         *
         * This is registered once, centrally, rather than every future
         * Policy method starting with `if ($actor->hasRole('Super Admin'))
         * return true;` — that repetition is exactly the kind of duplicated
         * authorization logic LEVEL 38 warns against, and a role meant to
         * mean "everything" should not depend on every future Policy
         * remembering to special-case it.
         */
        Gate::before(function (User $actor, string $ability) {
            return $actor->hasRole(RoleName::SuperAdmin->value) ? true : null;
        });
    }
}
