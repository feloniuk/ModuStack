<?php

namespace App\Core\Billing\Providers;

use App\Core\Billing\Contracts\PaymentGatewayInterface;
use App\Models\User;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FondyPaymentGateway implements PaymentGatewayInterface 
{
    private const API_URL = 'https://api.fondy.eu/api/';

    public function createSubscription(
        User $user, 
        Plan $plan, 
        string $paymentMethod
    ): Subscription {
        $response = Http::withToken(config('services.fondy.merchant_key'))
            ->post(self::API_URL . 'subscriptions', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'amount' => $plan->price * 100, // копейки
                'currency' => 'UAH',
                'interval' => 'month'
            ]);

        if (!$response->successful()) {
            throw new \Exception('Не удалось создать подписку');
        }

        $responseData = $response->json();

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'payment_gateway' => 'fondy',
            'gateway_subscription_id' => $responseData['subscription_id'],
            'status' => 'pending',
            'starts_at' => now(),
            'ends_at' => now()->addMonth()
        ]);
    }

    public function cancelSubscription(Subscription $subscription): bool 
    {
        $response = Http::withToken(config('services.fondy.merchant_key'))
            ->post(self::API_URL . 'subscriptions/cancel', [
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
        // Логика обработки вебхука Fondy
        // Проверка подписи, создание платежа
        if (!$this->verifyWebhookSignature($payload)) {
            return null;
        }

        return Payment::create([
            'user_id' => $payload['user_id'],
            'amount' => $payload['amount'] / 100,
            'gateway' => 'fondy',
            'transaction_id' => $payload['transaction_id'],
            'status' => $payload['status'],
            'gateway_response' => $payload
        ]);
    }

    public function verifyPayment(string $transactionId): bool 
    {
        $response = Http::withToken(config('services.fondy.merchant_key'))
            ->get(self::API_URL . 'payments/' . $transactionId);

        return $response->successful() && 
               $response->json('status') === 'completed';
    }

    private function verifyWebhookSignature(array $payload): bool 
    {
        // Реализация проверки подписи Fondy
        // Используйте merchant_key и secret из конфигурации
        return true; // Заглушка, реальная проверка требует реализации
    }
}