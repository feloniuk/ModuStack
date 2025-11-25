<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\AiRequest;
use App\Models\Assistant;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    public function getUserAnalytics(User $user, $period = 30)
    {
        $startDate = now()->subDays($period);

        return [
            'ai_requests' => $this->getAIRequestStats($user, $startDate),
            'assistants_usage' => $this->getAssistantsUsage($user, $startDate),
            'projects_activity' => $this->getProjectsActivity($user, $startDate),
            'token_consumption' => $this->getTokenConsumption($user, $startDate),
        ];
    }

    private function getAIRequestStats(User $user, $startDate)
    {
        return AiRequest::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(tokens_used) as total_tokens'),
                DB::raw('AVG(tokens_used) as avg_tokens')
            )
            ->get();
    }

    private function getAssistantsUsage(User $user, $startDate)
    {
        return Assistant::where('user_id', $user->id)
            ->withCount(['aiRequests' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->get()
            ->map(function ($assistant) {
                return [
                    'name' => $assistant->name,
                    'total_requests' => $assistant->ai_requests_count,
                    'model' => $assistant->model
                ];
            });
    }

    private function getProjectsActivity(User $user, $startDate)
    {
        return Project::where('user_id', $user->id)
            ->withCount(['assistants', 'aiRequests' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->get()
            ->map(function ($project) {
                return [
                    'name' => $project->name,
                    'assistants_count' => $project->assistants_count,
                    'ai_requests_count' => $project->ai_requests_count
                ];
            });
    }

    private function getTokenConsumption(User $user, $startDate)
    {
        return AiRequest::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->groupBy('provider_id')
            ->select(
                'provider_id',
                DB::raw('SUM(tokens_used) as total_tokens'),
                DB::raw('COUNT(*) as request_count')
            )
            ->with('provider')
            ->get()
            ->map(function ($stat) {
                return [
                    'provider_name' => $stat->provider->name,
                    'total_tokens' => $stat->total_tokens,
                    'request_count' => $stat->request_count
                ];
            });
    }

    public function generatePredictiveInsights(User $user)
    {
        $recentRequests = AiRequest::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(60))
            ->get();

        $modelUsage = $recentRequests->groupBy('model_name')
            ->map(function ($requests) {
                return [
                    'model' => $requests->first()->model_name,
                    'usage_count' => $requests->count(),
                    'avg_tokens' => $requests->avg('tokens_used')
                ];
            })
            ->sortByDesc('usage_count')
            ->values();

        $predictiveTrends = [
            'most_used_model' => $modelUsage->first(),
            'total_monthly_tokens_projection' => $recentRequests->sum('tokens_used') * 2,
            'recommended_plan' => $this->recommendPlan($user, $modelUsage)
        ];

        return $predictiveTrends;
    }

    private function recommendPlan(User $user, $modelUsage)
    {
        $currentPlan = $user->currentPlan();
        $totalTokens = $modelUsage->sum('usage_count');

        if ($totalTokens > 50000) {
            return 'Business';
        } elseif ($totalTokens > 10000) {
            return 'Pro';
        }

        return 'Basic';
    }
}