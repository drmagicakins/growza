<?php

namespace App\Providers;

use App\Domain\Auth\Actions\CreateNewUser;
use App\Domain\Auth\Actions\ResetUserPassword;
use App\Domain\Auth\Actions\UpdateUserPassword;
use App\Domain\Auth\Actions\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

/**
 * Wires Fortify to Growza's own domain Actions and Blade views.
 *
 * Fortify is used headless: it owns the routes and the plumbing, while
 * every piece of business logic (validation rules, audit writes, security
 * notifications) lives in App\Domain\Auth\Actions where it can be unit
 * tested without HTTP. See ARCHITECTURE.md §6.
 */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        $this->registerViews();
        $this->registerRateLimiters();
    }

    private function registerViews(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
    }

    private function registerRateLimiters(): void
    {
        /*
         * Login is limited on email AND IP together rather than on email
         * alone. Limiting by email alone lets an attacker lock a known user
         * out of their own account simply by failing logins against it from
         * anywhere — turning a brute-force defence into a denial-of-service
         * tool. Combining both keys means an attacker throttles only
         * themselves.
         */
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input(Fortify::username());
            $throttleKey = Str::transliterate(Str::lower($email).'|'.$request->ip());

            return [
                Limit::perMinute((int) env('LOGIN_THROTTLE_MAX_ATTEMPTS', 5))->by($throttleKey),
                // A wider per-IP ceiling catches credential-stuffing that
                // rotates through many different email addresses.
                Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
