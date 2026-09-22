<?php

namespace App\Support;

/**
 * Formats an integer minor-unit amount (kobo) for display.
 *
 * Deliberately presentation-only — no arithmetic, no persistence, no
 * business rules. LEVEL 8's WalletService owns the actual money logic
 * (crediting, debiting, idempotency); this class only ever turns a number
 * that already exists into a string a human can read. Naira-only for now,
 * matching the platform's single-currency v1.0 scope (DATABASE.md).
 */
final class Money
{
    public static function format(int $minorUnits, string $currency = 'NGN'): string
    {
        $symbol = match ($currency) {
            'NGN' => '₦',
            default => $currency.' ',
        };

        return $symbol.number_format($minorUnits / 100, 2);
    }
}
