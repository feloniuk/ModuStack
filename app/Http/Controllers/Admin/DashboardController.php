<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Subscription;
use App\Models\AiRequest;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::whereHas('subscriptions', fn($q) => $q->where('status', 'active'))->count(),
                'total_plans' => Plan::count(),
                'total_providers' => Provider::count()
            ],
            'revenue_stats' => $this->getRevenueStatistics(),
            'usage_stats' => $this->getUsageStatistics(),
            'recent_activity' => $this->getRecentActivity()
        ]);
    }

    private function getRevenueStatistics()
    {
        return [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->where('created_at', '>=', now()->subMonth())
                ->sum('amount'),
            'revenue_by_plan' => Plan::withSum('subscriptions', 'price')->get()
        ];
    }

    private function getUsageStatistics()
    {
        return [
            'total_ai_requests' => AiRequest::count(),
            'monthly_ai_requests' => AiRequest::where('created_at', '>=', now()->subMonth())->count(),
            'tokens_by_provider' => AiRequest::groupBy('provider_id')
                ->select('provider_id', DB::raw('SUM(tokens_used) as total_tokens'))
                ->get()
        ];
    }

    private function getRecentActivity()
    {
        return [
            'recent_users' => User::latest()->limit(10)->get(),
            'recent_subscriptions' => Subscription::with('user', 'plan')->latest()->limit(10)->get(),
            'recent_payments' => Payment::with('user')->latest()->limit(10)->get()
        ];
    }
}