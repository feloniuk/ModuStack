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
            // Существующая статистика
            'total_users' => User::count(),
            'total_active_users' => User::whereHas('subscriptions', function($q) {
                $q->where('status', 'active');
            })->count(),
            
            // Новые блоки статистики
            'total_plans' => Plan::count(),
            'total_providers' => Provider::count(),
            'total_subscriptions' => Subscription::count(),
            'total_ai_requests' => AiRequest::count(),
            
            // Расширенная статистика
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
            'revenue_by_plan' => Plan::withSum('subscriptions as total_revenue', 'price')
                ->get()
                ->pluck('total_revenue', 'name')
        ];
    }

    private function getUsageStatistics()
    {
        return [
            'total_tokens_used' => AiRequest::sum('tokens_used'),
            'monthly_tokens' => AiRequest::where('created_at', '>=', now()->subMonth())->sum('tokens_used'),
            'tokens_by_provider' => AiRequest::groupBy('provider_id')
                ->selectRaw('provider_id, SUM(tokens_used) as total_tokens')
                ->with('provider')
                ->get()
        ];
    }

    private function getRecentActivity()
    {
        return [
            'recent_users' => User::latest()->limit(10)->get(),
            'recent_ai_requests' => AiRequest::with(['user', 'provider'])->latest()->limit(20)->get(),
            'recent_subscriptions' => Subscription::with(['user', 'plan'])->latest()->limit(10)->get(),
            'recent_payments' => Payment::with(['user', 'subscription'])->latest()->limit(10)->get()
        ];
    }
}