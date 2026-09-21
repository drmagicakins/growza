<?php

namespace App\Domain\Identity\Enums;

/**
 * The nine platform-wide roles (LEVEL 4). Backed by string so the value
 * IS the exact name spatie/laravel-permission stores — no separate
 * mapping table to keep in sync.
 *
 * These are GLOBAL roles (team_id = null in the `roles` table). Team-scoped
 * roles for Agencies (LEVEL 21-22: Agency Owner, Manager, Finance, Campaign
 * Manager, Viewer) are a deliberately separate mechanism — the plain
 * `team_user.team_role` string column created at LEVEL 0 — not additional
 * values on this enum. Mixing the two would let an agency's internal team
 * role be mistaken for platform-wide authority.
 */
enum RoleName: string
{
    case SuperAdmin = 'Super Admin';
    case Administrator = 'Administrator';
    case FinanceManager = 'Finance Manager';
    case OrderManager = 'Order Manager';
    case ProviderManager = 'Provider Manager';
    case SupportAgent = 'Support Agent';
    case ContentManager = 'Content Manager';
    case Reseller = 'Reseller';
    case Customer = 'Customer';

    /**
     * Every newly registered public user gets this role. Anything above it
     * is granted by an existing administrator, never by self-registration.
     */
    public static function default(): self
    {
        return self::Customer;
    }
}
