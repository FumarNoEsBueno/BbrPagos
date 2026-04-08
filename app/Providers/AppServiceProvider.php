<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\Gateways\MockPaymentGateway;
use App\Infrastructure\Gateways\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar el gateway de pagos
        // Cambiar a la implementación real cuando se tenga el provider
        $this->app->singleton(PaymentGatewayInterface::class, function ($app) {
            return new MockPaymentGateway();
        });
    }

    public function boot(): void
    {
        //
    }
}
