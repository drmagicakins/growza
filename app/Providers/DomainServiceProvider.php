<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Central place where Domain\*\Contracts are bound to their concrete
 * implementations (e.g. PaymentGatewayInterface -> PaystackGateway,
 * ServiceProviderInterface -> the configured provider adapter).
 *
 * Deliberately empty at LEVEL 0 — bindings are added by the level that
 * introduces the contract (LEVEL 9 for payments, LEVEL 10 for providers),
 * so this file's diff history doubles as a log of when each abstraction
 * was wired up.
 */
class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
