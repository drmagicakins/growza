<?php

use App\Domain\Identity\Models\User;
use App\Domain\Reporting\Services\CustomerDashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

// This test touches the users table, so it needs a migrated database —
// the Unit suite does not get RefreshDatabase by default (see Pest.php),
// so it is added explicitly here, the same pattern used in
// tests/Unit/UserPolicyTest.php.
uses(RefreshDatabase::class);

it('returns every metric as zero until the owning domains exist', function () {
    $user = User::forceCreate([
        'name' => 'Metrics Test',
        'email' => 'metrics@example.com',
        'phone' => '+2348011119999',
        'password' => bcrypt('correct-horse-99'),
    ]);

    $metrics = (new CustomerDashboardMetricsService)->forUser($user);

    expect($metrics->walletBalanceMinor)->toBe(0)
        ->and($metrics->totalOrders)->toBe(0)
        ->and($metrics->activeOrders)->toBe(0)
        ->and($metrics->completedOrders)->toBe(0)
        ->and($metrics->totalSpentMinor)->toBe(0)
        ->and($metrics->referralEarningsMinor)->toBe(0);
});
