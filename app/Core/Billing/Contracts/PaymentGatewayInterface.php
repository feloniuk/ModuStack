<?php

namespace App\Core\Billing\Contracts;

use App\Models\User;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\Subscription;

interface PaymentGatewayInterface 
{
    public function createSubscription(
        User $user, 
        Plan $plan, 
        string $paymentMethod
    ): Subscription;

    public function cancelSubscription(
        Subscription $subscription
    ): bool;

    public function processWebhook(array $payload): ?Payment;

    public function verifyPayment(string $transactionId): bool;
}