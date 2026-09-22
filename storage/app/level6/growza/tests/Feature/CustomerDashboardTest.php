<?php

use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

function dashboardUser(array $overrides = []): User
{
    $user = User::forceCreate(array_merge([
        'name' => 'Funke Adio',
        'email' => 'funke@example.com',
        'phone' => '+2348022223333',
        'password' => Hash::make('correct-horse-99'),
        'email_verified_at' => now(),
        'status' => 'active',
    ], $overrides));
    $user->assignRole(\App\Domain\Identity\Enums\RoleName::Customer->value);

    return $user;
}

it('renders every dashboard page for an authenticated, verified customer', function (string $routeName) {
    actingAs(dashboardUser())->get(route($routeName))->assertOk();
})->with([
    'dashboard',
    'dashboard.profile',
    'dashboard.services',
    'dashboard.orders',
    'dashboard.wallet',
    'dashboard.transactions',
    'dashboard.referrals',
    'dashboard.support',
    'dashboard.notifications',
    'dashboard.settings',
]);

it('redirects a guest to login for every dashboard page', function (string $routeName) {
    get(route($routeName))->assertRedirect(route('login'));
})->with([
    'dashboard',
    'dashboard.profile',
    'dashboard.wallet',
    'dashboard.settings',
]);

it('sends an unverified user to the verification notice instead of the dashboard', function () {
    actingAs(dashboardUser(['email_verified_at' => null]))
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('ejects a suspended user from the dashboard', function () {
    actingAs(dashboardUser(['status' => 'suspended']))
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('shows every metric as genuinely zero, not a fake number', function () {
    $response = actingAs(dashboardUser())->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('₦0.00', escape: false)
        ->assertSee('No campaigns yet', escape: false);
});

it('shows the referral code a user registered with, if any', function () {
    $user = dashboardUser(['email' => 'referred@example.com']);
    $user->forceFill(['referred_by_code' => 'GZFRIEND1'])->save();

    actingAs($user)->get(route('dashboard.referrals'))->assertSee('GZFRIEND1', escape: false);
});

it('does not show a referral banner for a user with no referral code', function () {
    actingAs(dashboardUser())->get(route('dashboard.referrals'))
        ->assertDontSee('registered with the referral code', escape: false);
});

it('updates the profile via the existing Fortify endpoint', function () {
    $user = dashboardUser();

    actingAs($user)->put(route('user-profile-information.update'), [
        'name' => 'Funke Adio-Balogun',
        'email' => $user->email,
        'phone' => '+2348044445555',
    ])->assertSessionHas('status', 'profile-information-updated');

    expect($user->fresh()->name)->toBe('Funke Adio-Balogun')
        ->and($user->fresh()->phone)->toBe('+2348044445555');
});

it('does not register a route for a single order yet', function () {
    // Deliberate LEVEL 5 scope limit — see routes/auth.php. Guards against
    // someone adding a fake {order} route without the model behind it.
    actingAs(dashboardUser())->get('/dashboard/orders/1')->assertNotFound();
});
