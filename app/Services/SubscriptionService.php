<?php

namespace App\Services;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Core\Billing\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    private PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function subscribeToPlan(User $user, Plan $plan): Subscription
    {
        // Отменить существующие активные подписки
        $user->subscriptions()
             ->where('status', 'active')
             ->update(['status' => 'canceled']);

        try {
            $subscription = $this->gateway->createSubscription(
                $user, 
                $plan, 
                'card'
            );

            // Логирование
            Log::channel('subscriptions')->info('New Subscription', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'gateway' => $subscription->payment_gateway
            ]);

            return $subscription;

        } catch (\Exception $e) {
            Log::channel('subscriptions')->error('Subscription Error', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        try {
            $result = $this->gateway->cancelSubscription($subscription);

            Log::channel('subscriptions')->info('Subscription Canceled', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::channel('subscriptions')->error('Cancellation Error', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}