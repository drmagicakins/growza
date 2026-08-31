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
];
