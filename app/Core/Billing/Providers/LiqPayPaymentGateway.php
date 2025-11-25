<?php

namespace App\Core\Billing\Providers;

use App\Core\Billing\Contracts\PaymentGatewayInterface;
use App\Models\User;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LiqPayPaymentGateway implements PaymentGatewayInterface 
{
    private const API_URL = 'https://www.liqpay.ua/api/';

    public function createSubscription(
        User $user, 
        Plan $plan, 
        string $paymentMethod
    ): Subscription {
        $response = Http::withToken(config('services.liqpay.public_key'))
            ->post(self::API_URL . 'request', [
                'action' => 'subscribe',
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'amount' => $plan->price,
                'currency' => 'UAH',
                'description' => "Подписка на план {$plan->name}"
            ]);

        if (!$response->successful()) {
            throw new \Exception('Не удалось создать подписку в LiqPay');
        }

        $responseData = $response->json();

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'payment_gateway' => 'liqpay',
            'gateway_subscription_id' => $responseData['subscription_id'],
            'status' => 'pending',
            'starts_at' => now(),
            'ends_at' => now()->addMonth()
        ]);
    }

    public function cancelSubscription(Subscription $subscription): bool 
    {
        $response = Http::withToken(config('services.liqpay.public_key'))
            ->post(self::API_URL . 'unsubscribe', [
                'subscription_id' => $subscription->gateway_subscription_id
            ]);

        if ($response->successful()) {
            $subscription->update([
                'status' => 'canceled',
                'ends_at' => now()
            ]);
            return true;
        }

        return false;
    }

    public function processWebhook(array $payload): ?Payment 
    {
        if (!$this->verifyWebhookSignature($payload)) {
            return null;
        }

        return Payment::create([
            'user_id' => $payload['user_id'],
            'amount' => $payload['amount'],
            'gateway' => 'liqpay',
            'transaction_id' => $payload['transaction_id'],
            'status' => $this->mapPaymentStatus($payload['status']),
            'gateway_response' => $payload
        ]);
    }

    public function verifyPayment(string $transactionId): bool 
    {
        $response = Http::withToken(config('services.liqpay.public_key'))
            ->get(self::API_URL . 'status', [
                'transaction_id' => $transactionId
            ]);

        return $response->successful() && 
               $this->mapPaymentStatus($response->json('status')) === 'completed';
    }

    private function verifyWebhookSignature(array $payload): bool 
    {
        // Реальная проверка подписи LiqPay
        return true; // Заглушка
    }

    private function mapPaymentStatus(string $status): string 
    {
        $statusMap = [
            'success' => 'completed',
            'error' => 'failed',
            'processing' => 'pending',
            'wait_accept' => 'pending',
            'wait_secure' => 'pending'
        ];

        return $statusMap[$status] ?? 'pending';
    }
}