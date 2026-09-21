<?php

use App\Support\Money;

it('formats naira minor units with the naira symbol', function () {
    expect(Money::format(0))->toBe('₦0.00')
        ->and(Money::format(450000))->toBe('₦4,500.00')
        ->and(Money::format(50))->toBe('₦0.50');
});

it('falls back to a currency-code prefix for a non-naira currency', function () {
    expect(Money::format(100000, 'USD'))->toBe('USD 1,000.00');
});
