<?php

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Auth\Notifications\PasswordChangedNotification;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

beforeEach(fn () => Cache::flush());

function securityUser(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'name' => 'Ngozi Udeh',
        'email' => 'ngozi@example.com',
        'phone' => '+2348055556666',
        'password' => Hash::make('correct-horse-99'),
        'email_verified_at' => now(),
        'status' => 'active',
    ], $overrides));
}

it('updates a password and notifies the account owner', function () {
    Notification::fake();
    $user = securityUser();

    actingAs($user)->put(route('user-password.update'), [
        'current_password' => 'correct-horse-99',
        'password' => 'brand-new-secret-42',
        'password_confirmation' => 'brand-new-secret-42',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('brand-new-secret-42', $user->fresh()->password))->toBeTrue();

    Notification::assertSentTo($user, PasswordChangedNotification::class);
});

it('rejects a password change without the correct current password', function () {
    $user = securityUser();

    actingAs($user)->put(route('user-password.update'), [
        'current_password' => 'not-the-right-one',
        'password' => 'brand-new-secret-42',
        'password_confirmation' => 'brand-new-secret-42',
    ])->assertSessionHasErrors('current_password', errorBag: 'updatePassword');

    expect(Hash::check('correct-horse-99', $user->fresh()->password))->toBeTrue();
});

it('audits a password change without recording the password', function () {
    $user = securityUser();

    actingAs($user)->put(route('user-password.update'), [
        'current_password' => 'correct-horse-99',
        'password' => 'brand-new-secret-42',
        'password_confirmation' => 'brand-new-secret-42',
    ]);

    $log = AuditLog::where('action', 'user.password_changed')->first();

    expect($log)->not->toBeNull()
        ->and(json_encode($log->toArray()))->not->toContain('brand-new-secret-42');
});

it('ejects a suspended user from authenticated routes', function () {
    $user = securityUser(['status' => 'suspended']);

    actingAs($user)->get(route('settings.security'))->assertRedirect(route('login'));

    expect(auth()->check())->toBeFalse();
});

it('ejects a banned user from authenticated routes', function () {
    actingAs(securityUser(['status' => 'banned']))
        ->get(route('settings.security'))
        ->assertRedirect(route('login'));
});

it('lets an active user reach security settings', function () {
    actingAs(securityUser())->get(route('settings.security'))->assertOk();
});

it('reports two-factor as disabled until it is confirmed', function () {
    $user = securityUser();

    expect($user->hasTwoFactorEnabled())->toBeFalse();

    // A secret alone is not enough — an unconfirmed secret means the user
    // never proved their authenticator app actually works.
    $user->forceFill(['two_factor_secret' => encrypt('dummy-secret')])->save();

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});
