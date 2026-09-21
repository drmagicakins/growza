<?php

namespace Database\Seeders;

use App\Domain\Identity\Enums\PermissionName;
use App\Domain\Identity\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the nine platform roles and eleven permissions from
 * ARCHITECTURE.md §7 / the master prompt's LEVEL 4 section, and grants
 * permissions to roles.
 *
 * Idempotent: uses firstOrCreate throughout, so running this against a
 * database that already has these roles/permissions updates the grants
 * without creating duplicates or erroring.
 */
class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Role => permissions granted. Roles not listed here (Customer,
     * Reseller) get none of these — they are gated by ownership checks in
     * their own domain's Policies (e.g. a Customer views their own orders
     * via OrderPolicy, not via a blanket `view_orders` permission), not by
     * this admin-facing permission set.
     *
     * @var array<string, array<int, string>>
     */
    private const GRANTS = [
        // Administrator: broad operational access, but NOT settings or
        // permanent user deletion — those stay with Super Admin so a
        // compromised Administrator account cannot lock out the platform
        // owner or rewrite its own authorization rules.
        'Administrator' => [
            'view_users', 'create_users', 'suspend_users',
            'view_orders', 'manage_orders',
            'view_payments', 'manage_refunds',
            'manage_services', 'manage_providers',
            'view_reports',
        ],

        'Finance Manager' => [
            'view_payments', 'manage_refunds', 'view_reports',
        ],

        'Order Manager' => [
            'view_orders', 'manage_orders', 'view_reports',
        ],

        'Provider Manager' => [
            'manage_providers', 'view_reports',
        ],

        'Support Agent' => [
            // Support needs to see who they're talking to and what they
            // ordered, but not touch money or infrastructure.
            'view_users', 'view_orders',
        ],

        'Content Manager' => [
            'manage_services',
        ],
    ];

    public function run(): void
    {
        // Spatie caches permission-to-role lookups aggressively; stale
        // cache after a reseed would silently serve old grants.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionName::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (RoleName::cases() as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName->value, 'guard_name' => 'web']);

            $grants = self::GRANTS[$roleName->value] ?? [];

            if ($grants !== []) {
                $role->syncPermissions($grants);
            }
        }

        // Super Admin intentionally holds no explicit permission grants
        // here. It bypasses every check via the Gate::before callback in
        // AuthServiceProvider instead of a maintained list — a role meant
        // to mean "everything" should not depend on this list staying
        // exhaustive as new permissions are added by later levels.

        Cache::forget(config('permission.cache.key', 'spatie.permission.cache'));
    }
}
