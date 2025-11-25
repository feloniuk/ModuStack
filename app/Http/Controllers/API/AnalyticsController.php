<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    private $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function getUserAnalytics()
    {
        $user = Auth::user();
        
        $analytics = $this->analyticsService->getUserAnalytics($user);
        $predictiveInsights = $this->analyticsService->generatePredictiveInsights($user);

        return response()->json([
            'user_analytics' => $analytics,
            'predictive_insights' => $predictiveInsights
        ]);
    }

    public function getDetailedAnalytics($period = 30)
    {
        $user = Auth::user();
        
        $analytics = $this->analyticsService->getUserAnalytics($user, $period);

        return response()->json($analytics);
    }
}