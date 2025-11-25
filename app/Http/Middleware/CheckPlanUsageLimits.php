<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UsageService;

class CheckPlanUsageLimits
{
    private UsageService $usageService;

    public function __construct(UsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $tokensUsed = $request->input('tokens_used', 100); // Базовое значение

        if (!$this->usageService->checkUsageLimits($user, $tokensUsed)) {
            return response()->json([
                'message' => 'Превышен суточный или месячный лимит токенов',
                'status' => 'limit_exceeded'
            ], 429);
        }

        return $next($request);
    }
}