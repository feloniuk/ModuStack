<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderManagementController extends Controller
{
    public function index()
    {
        $providers = Provider::withCount(['aiRequests'])
            ->get()
            ->map(function ($provider) {
                $provider->recent_success_rate = $this->calculateSuccessRate($provider);
                return $provider;
            });

        return response()->json([
            'providers' => $providers,
            'total_providers' => $providers->count()
        ]);
    }

    public function updateStatus(Provider $provider, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,degraded'
        ]);

        $provider->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'message' => 'Статус провайдера обновлен',
            'provider' => $provider
        ]);
    }

    private function calculateSuccessRate(Provider $provider): float
    {
        $totalRequests = $provider->aiRequests()->count();
        $successfulRequests = $provider->aiRequests()
            ->where('status', 'completed')
            ->count();

        return $totalRequests > 0 
            ? round(($successfulRequests / $totalRequests) * 100, 2) 
            : 0;
    }
}