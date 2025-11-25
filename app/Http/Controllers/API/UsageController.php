<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\UsageService;
use Illuminate\Support\Facades\Auth;

class UsageController extends Controller
{
    private UsageService $usageService;

    public function __construct(UsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function index(string $resourceType = null)
    {
        $user = Auth::user();
        
        $usageStatistics = $this->usageService->getUserUsageStatistics(
            $user, 
            $resourceType
        );

        return response()->json([
            'user_id' => $user->id,
            'current_subscription' => $user->subscriptions()->active()->first(),
            'usage_statistics' => $usageStatistics
        ]);
    }
}