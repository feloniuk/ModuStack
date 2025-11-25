<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Core\Billing\Providers\FondyPaymentGateway;
use App\Core\Billing\Providers\LiqPayPaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleFondy(Request $request, FondyPaymentGateway $gateway)
    {
        try {
            $payload = $request->all();
            Log::channel('payments')->info('Fondy Webhook', $payload);

            $payment = $gateway->processWebhook($payload);

            return $payment 
                ? response()->json(['status' => 'success']) 
                : response()->json(['status' => 'error'], 400);

        } catch (\Exception $e) {
            Log::channel('payments')->error('Fondy Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    public function handleLiqPay(Request $request, LiqPayPaymentGateway $gateway)
    {
        try {
            $payload = $request->all();
            Log::channel('payments')->info('LiqPay Webhook', $payload);

            $payment = $gateway->processWebhook($payload);

            return $payment 
                ? response()->json(['status' => 'success']) 
                : response()->json(['status' => 'error'], 400);

        } catch (\Exception $e) {
            Log::channel('payments')->error('LiqPay Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}