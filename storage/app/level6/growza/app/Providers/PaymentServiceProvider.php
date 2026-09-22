<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Binds App\Domain\Payments\Contracts\PaymentGatewayInterface to the
 * environment's configured default gateway, and registers named bindings
 * ('paystack', 'flutterwave') so PaymentService can resolve a specific
 * gateway when the user selects one explicitly at checkout.
 *
 * Left unimplemented at LEVEL 0 — populated at LEVEL 9. Declaring the
 * provider class now (registered in bootstrap/providers.php) means
 * LEVEL 9 only has to fill this file in, not also wire up bootstrapping.
 */
class PaymentServiceProvider extends ServiceProvider
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
