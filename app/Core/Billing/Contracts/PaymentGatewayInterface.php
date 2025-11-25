<?php

namespace App\Core\Billing\Contracts;

use App\Models\User;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\Subscription;

abstract class PaymentGatewayInterface 
{
    abstract public function createSubscription(
        User $user, 
        Plan $plan, 
        string $paymentMethod
    ): Subscription;

    abstract public function cancelSubscription(
        Subscription $subscription
    ): bool;

    abstract public function processWebhook(array $payload): ?Payment;

    abstract public function verifyPayment(string $transactionId): bool;
}