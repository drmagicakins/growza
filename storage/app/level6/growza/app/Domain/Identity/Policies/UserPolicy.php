<?php

namespace App\Domain\Identity\Policies;

use App\Domain\Identity\Enums\RoleName;
use App\Domain\Identity\Models\User;

/**
 * Authorization for viewing and managing other users' accounts.
 *
 * This is deliberately the first Policy written in the codebase (LEVEL 4)
 * to establish the pattern every later domain follows: a controller calls
 * `$this->authorize('suspend', $targetUser)`, never
 * `if ($user->hasRole('Administrator'))` inline. The permission check
 * lives here, once, where it can be tested in isolation.
 */
class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('view_users');
    }

    public function view(User $actor, User $target): bool
    {
        // A user can always view their own account, independent of the
        // admin-facing view_users permission.
        return $actor->is($target) || $actor->can('view_users');
    }

    public function create(User $actor): bool
    {
        return $actor->can('create_users');
    }

    public function suspend(User $actor, User $target): bool
    {
        // Nobody suspends themselves, permission or not — an admin who
        // wants out uses account deletion/deactivation flows, not this
        // action, so a compromised session can't be used to quietly
        // disable audit-trail continuity by suspending its own actor.
        if ($actor->is($target)) {
            return false;
        }

        // A Super Admin cannot be suspended by a lower-privileged
        // Administrator — otherwise "manage users" would transitively
        // include "lock out the platform owner".
        if ($target->hasRole(RoleName::SuperAdmin->value) && ! $actor->hasRole(RoleName::SuperAdmin->value)) {
            return false;
        }

        return $actor->can('suspend_users');
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->is($target) || $actor->can('view_users');
    }
}
