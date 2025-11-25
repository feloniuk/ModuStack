<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Subscription;
use App\Models\AiRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_active_users' => User::whereHas('subscriptions', function($q) {
                $q->where('status', 'active');
            })->count(),
            'total_plans' => Plan::count(),
            'total_providers' => Provider::count(),
            'total_subscriptions' => Subscription::count(),
            'total_ai_requests' => AiRequest::count(),
            'recent_activity' => $this->getRecentActivity()
        ]);
    }

    private function getRecentActivity()
    {
        return [
            'users' => User::latest()->limit(10)->get(),
            'ai_requests' => AiRequest::with(['user', 'provider'])->latest()->limit(20)->get(),
            'subscriptions' => Subscription::with(['user', 'plan'])->latest()->limit(10)->get()
        ];
    }
}