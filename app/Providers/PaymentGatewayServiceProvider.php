<?php

namespace App\Providers;

use App\Core\Billing\Contracts\PaymentGatewayInterface;
use App\Core\Billing\Providers\FondyPaymentGateway;
use App\Core\Billing\Providers\LiqPayPaymentGateway;
use Illuminate\Support\ServiceProvider;

class PaymentGatewayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            $region = config('app.region', 'UA');
            
            return match($region) {
                'UA' => $app->make(FondyPaymentGateway::class),
                default => $app->make(LiqPayPaymentGateway::class)
            };
        });
    }
}