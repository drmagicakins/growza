<?php

use App\Domain\Identity\Models\User;
use Laravel\Fortify\Features;

return [

    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',

    /*
    | Blade-driven auth screens (not an SPA), so Fortify's view routes are
    | enabled and the views themselves are registered in
    | App\Providers\FortifyServiceProvider.
    */
    'views' => true,

    'home' => '/dashboard',

    'prefix' => '',
    'domain' => null,

    'middleware' => ['web'],

    /*
    | Named rate limiters, defined in FortifyServiceProvider. Login is
    | throttled on email+IP together so one attacker cannot lock out a
    | legitimate user by hammering their address from elsewhere.
    */
    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    'lowercase_usernames' => true,

    /*
    | The Growza User model lives in the Identity domain rather than
    | App\Models — see ARCHITECTURE.md §3.
    */
    'models' => [
        'user' => User::class,
    ],

    'redirects' => [
        'login' => '/dashboard',
        'logout' => '/',
        'password-confirmation' => null,
        'register' => '/dashboard',
        'email-verification' => '/dashboard',
        'password-reset' => '/login',
    ],

    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),

        /*
        | 2FA is optional per user at v1.0 and requires password
        | confirmation to enable or disable, so a hijacked session cannot
        | silently turn it off. ARCHITECTURE.md §22 notes that making 2FA
        | mandatory for staff roles at v2.0 is a config change, not new
        | plumbing.
        */
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),

        /*
        | Passkeys are deliberately NOT enabled at v1.0. They are a real
        | Fortify feature, but enabling them commits us to a credential
        | recovery story (lost device, account takeover) that has not been
        | designed. Turning it on without that design would be a security
        | decision made by default rather than deliberately.
        */
    ],
];
