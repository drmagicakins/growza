<?php

namespace App\Domain\Identity\Enums;

/**
 * Granular permissions (LEVEL 4). This is the exact list named in the
 * master prompt's LEVEL 4 section, plus nothing else — permissions for
 * features that do not exist yet (e.g. manage_coupons, manage_providers'
 * finer actions) are added by the level that introduces that feature,
 * not pre-created here on the assumption they'll be needed.
 */
enum PermissionName: string
{
    case ViewUsers = 'view_users';
    case CreateUsers = 'create_users';
    case SuspendUsers = 'suspend_users';

    case ViewOrders = 'view_orders';
    case ManageOrders = 'manage_orders';

    case ViewPayments = 'view_payments';
    case ManageRefunds = 'manage_refunds';

    case ManageServices = 'manage_services';
    case ManageProviders = 'manage_providers';

    case ViewReports = 'view_reports';
    case ManageSettings = 'manage_settings';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
