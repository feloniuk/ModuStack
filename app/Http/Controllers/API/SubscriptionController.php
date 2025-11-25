<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index()
    {
        $subscriptions = Auth::user()->subscriptions()->with('plan')->get();
        
        return response()->json([
            'subscriptions' => $subscriptions,
            'current_subscription' => $subscriptions->where('status', 'active')->first()
        ]);
    }

    public function subscribe(int $planId)
    {
        $plan = Plan::findOrFail($planId);
        
        try {
            $subscription = $this->subscriptionService->subscribeToPlan(
                Auth::user(), 
                $plan
            );

            return response()->json([
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'plan' => $plan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Не удалось оформить подписку',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function cancel(int $subscriptionId)
    {
        $subscription = Auth::user()->subscriptions()->findOrFail($subscriptionId);
        
        $result = $this->subscriptionService->cancelSubscription($subscription);

        return response()->json([
            'success' => $result,
            'message' => $result 
                ? 'Подписка отменена' 
                : 'Не удалось отменить подписку'
        ]);
    }
}