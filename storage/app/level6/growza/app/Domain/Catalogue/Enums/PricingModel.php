<?php

namespace App\Domain\Catalogue\Enums;

/**
 * The two pricing shapes a service can have. See the `services` migration
 * docblock for the reasoning — there is deliberately no third
 * "quantity-based" model (follower/like/stream counts).
 */
enum PricingModel: string
{
    case Fixed = 'fixed';
    case BudgetRange = 'budget_range';
}
