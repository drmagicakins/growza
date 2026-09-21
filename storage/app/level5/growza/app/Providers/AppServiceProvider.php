<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS URL generation in any non-local environment, even if
        // the app sits behind a TLS-terminating proxy (see DEPLOYMENT.md).
        if (! $this->app->environment('local', 'testing')) {
            URL::forceScheme('https');
        }
    }
}
