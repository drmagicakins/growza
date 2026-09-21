<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Binds App\Domain\Providers\Contracts\ServiceProviderInterface adapters.
 * Populated at LEVEL 10. See ARCHITECTURE.md §10 — no named third-party
 * provider is selected in this codebase; PROVIDER_DEFAULT_ADAPTER=mock
 * in .env.example points at an isolated development/testing adapter
 * until a real provider's credentials and adapter class are supplied.
 */
class ProviderIntegrationServiceProvider extends ServiceProvider
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
