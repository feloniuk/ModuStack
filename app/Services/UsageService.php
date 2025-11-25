<?php

namespace App\Services;

use App\Models\User;
use App\Models\UsageLog;
use App\Models\Plan;
use Exception;

class UsageService
{
    public function checkUsageLimits(User $user, int $tokensUsed): bool
    {
        $currentSubscription = $user->subscriptions()->active()->first();
        
        if (!$currentSubscription) {
            return false;
        }

        $plan = $currentSubscription->plan;
        
        // Проверка лимитов плана
        $dailyTokenLimit = $plan->features['max_tokens_per_day'] ?? 10000;
        $monthlyTokenLimit = $plan->features['max_tokens_per_month'] ?? 100000;

        $dailyUsage = UsageLog::where('user_id', $user->id)
            ->where('log_date', now()->toDateString())
            ->sum('tokens_used');

        $monthlyUsage = UsageLog::where('user_id', $user->id)
            ->where('log_date', '>=', now()->subMonth()->toDateString())
            ->sum('tokens_used');

        return (
            $dailyUsage + $tokensUsed <= $dailyTokenLimit &&
            $monthlyUsage + $tokensUsed <= $monthlyTokenLimit
        );
    }

    public function recordUsage(
        User $user, 
        string $resourceType, 
        int $tokensUsed, 
        array $metadata = []
    ): UsageLog {
        return UsageLog::logUsage($user, $resourceType, $tokensUsed, $metadata);
    }

    public function getUserUsageStatistics(User $user, ?string $resourceType = null)
    {
        $query = UsageLog::where('user_id', $user->id);

        if ($resourceType) {
            $query->where('resource_type', $resourceType);
        }

        return [
            'daily_usage' => $query->clone()
                ->where('log_date', now()->toDateString())
                ->get(),
            'monthly_usage' => $query->clone()
                ->where('log_date', '>=', now()->subMonth()->toDateString())
                ->get(),
            'total_usage' => $query->get()
        ];
    }
}