<?php

return [
    App\Providers\AppServiceProvider::class,

    // Growza domain-level providers (created empty at LEVEL 0; each is
    // populated as its corresponding batch/level is implemented — see
    // PROJECT_STATE.md for what each currently does and does not bind).
    App\Providers\DomainServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\PaymentServiceProvider::class,
    App\Providers\ProviderIntegrationServiceProvider::class,

    // Wires Fortify to Growza's own Auth domain actions and Blade views
    // (LEVEL 3). Fortify is headless: it owns the routes and plumbing, while
    // validation, audit writes and security notifications live in
    // App\Domain\Auth\Actions so they can be unit tested without HTTP.
    App\Providers\FortifyServiceProvider::class,
];
