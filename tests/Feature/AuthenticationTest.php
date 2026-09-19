<?php

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(fn () => Cache::flush());

function makeUser(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'name' => 'Akin Balogun',
        'email' => 'akin@example.com',
        'phone' => '+2348011112222',
        'password' => Hash::make('correct-horse-99'),
        'email_verified_at' => now(),
        'status' => 'active',
    ], $overrides));
}

it('renders the login and register screens', function () {
    get(route('login'))->assertOk()->assertSee('Welcome back', escape: false);
    get(route('register'))->assertOk()->assertSee('Create your account', escape: false);
});

it('registers a user with the required fields', function () {
    post(route('register'), [
        'name' => 'Chidi Eze',
        'email' => 'chidi@example.com',
        'phone' => '+2348033334444',
        'password' => 'correct-horse-99',
        'password_confirmation' => 'correct-horse-99',
        'terms' => '1',
    ])->assertRedirect();

    $user = User::where('email', 'chidi@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->phone)->toBe('+2348033334444')
        ->and($user->email_verified_at)->toBeNull();
});

it('records a referral code at registration without resolving it', function () {
    post(route('register'), [
        'name' => 'Chidi Eze',
        'email' => 'chidi2@example.com',
        'phone' => '+2348033335555',
        'password' => 'correct-horse-99',
        'password_confirmation' => 'correct-horse-99',
        'referral_code' => 'GZREF123',
        'terms' => '1',
    ]);

    // LEVEL 14 turns this into a commission. At LEVEL 3 it is only recorded.
    expect(User::where('email', 'chidi2@example.com')->first()->referred_by_code)->toBe('GZREF123');
});

it('refuses registration without accepting the terms', function () {
    post(route('register'), [
        'name' => 'Chidi Eze',
        'email' => 'noterms@example.com',
        'phone' => '+2348033336666',
        'password' => 'correct-horse-99',
        'password_confirmation' => 'correct-horse-99',
    ])->assertSessionHasErrors('terms');

    expect(User::where('email', 'noterms@example.com')->exists())->toBeFalse();
});

it('enforces a minimum password strength', function () {
    post(route('register'), [
        'name' => 'Weak Password',
        'email' => 'weak@example.com',
        'phone' => '+2348033337777',
        'password' => 'short1',
        'password_confirmation' => 'short1',
        'terms' => '1',
    ])->assertSessionHasErrors('password');
});

it('rejects a duplicate email and a duplicate phone', function () {
    makeUser();

    post(route('register'), [
        'name' => 'Copy Cat',
        'email' => 'akin@example.com',
        'phone' => '+2349099998888',
        'password' => 'correct-horse-99',
        'password_confirmation' => 'correct-horse-99',
        'terms' => '1',
    ])->assertSessionHasErrors('email');

    post(route('register'), [
        'name' => 'Copy Cat',
        'email' => 'different@example.com',
        'phone' => '+2348011112222',
        'password' => 'correct-horse-99',
        'password_confirmation' => 'correct-horse-99',
        'terms' => '1',
    ])->assertSessionHasErrors('phone');
});

it('logs a user in with correct credentials', function () {
    makeUser();

    post(route('login'), ['email' => 'akin@example.com', 'password' => 'correct-horse-99'])
        ->assertRedirect(route('dashboard'));

    expect(auth()->check())->toBeTrue();
});

it('rejects a wrong password', function () {
    makeUser();

    post(route('login'), ['email' => 'akin@example.com', 'password' => 'wrong-password-11'])
        ->assertSessionHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('writes an audit record for a successful login', function () {
    $user = makeUser();

    post(route('login'), ['email' => 'akin@example.com', 'password' => 'correct-horse-99']);

    expect(AuditLog::where('action', 'auth.login')->where('actor_id', $user->id)->exists())->toBeTrue();
});

it('writes an audit record for a failed login', function () {
    makeUser();

    post(route('login'), ['email' => 'akin@example.com', 'password' => 'wrong-password-11']);

    expect(AuditLog::where('action', 'auth.login_failed')->exists())->toBeTrue();
});

it('throttles repeated failed logins', function () {
    makeUser();

    // The first five failures are ordinary validation failures.
    foreach (range(1, 5) as $i) {
        post(route('login'), ['email' => 'akin@example.com', 'password' => 'wrong-password-11'])
            ->assertSessionHasErrors('email');
    }

    /*
     * The sixth is refused by the rate limiter, which responds with HTTP 429.
     *
     * Note on mechanism: because config('fortify.limiters.login') names a
     * limiter, Fortify deliberately skips its own EnsureLoginIsNotThrottled
     * pipe and the throttle is enforced by Laravel's ThrottleRequests
     * middleware instead. That middleware returns a plain 429 and does NOT
     * flash a "try again in :seconds seconds" message, so asserting on a
     * session error here would assert the wrong mechanism and can never pass.
     * The observable, user-facing fact is the 429 itself.
     */
    post(route('login'), ['email' => 'akin@example.com', 'password' => 'wrong-password-11'])
        ->assertStatus(429);

    // And the throttle must not have let the attacker in along the way.
    expect(auth()->check())->toBeFalse();
});

it('keeps the throttle key scoped to email and IP together', function () {
    makeUser();

    // Exhaust the limit for one address...
    foreach (range(1, 5) as $i) {
        post(route('login'), ['email' => 'akin@example.com', 'password' => 'wrong-password-11']);
    }

    post(route('login'), ['email' => 'akin@example.com', 'password' => 'wrong-password-11'])
        ->assertStatus(429);

    /*
     * ...then confirm a DIFFERENT email from the same IP still gets an ordinary
     * validation failure rather than inheriting the lockout. This is the whole
     * point of keying on email+IP: an attacker must not be able to lock a known
     * user out of their own account by failing logins against it from afar.
     */
    post(route('login'), ['email' => 'someone-else@example.com', 'password' => 'wrong-password-11'])
        ->assertSessionHasErrors('email');
});

it('keeps unauthenticated visitors out of the dashboard', function () {
    get(route('dashboard'))->assertRedirect(route('login'));
});

it('sends an unverified user to the verification notice', function () {
    actingAs(makeUser(['email_verified_at' => null]))
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('lets a verified user reach the dashboard', function () {
    actingAs(makeUser())->get(route('dashboard'))->assertOk();
});
