<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\Integrations\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    private $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    public function index()
    {
        $integrations = Auth::user()->integrations;

        return response()->json([
            'integrations' => $integrations,
            'available_types' => [
                'slack' => 'Slack',
                'discord' => 'Discord',
                'webhook' => 'Generic Webhook'
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:slack,discord,webhook',
            'webhook_url' => 'required|url',
            'access_token' => 'sometimes|string',
            'additional_config' => 'sometimes|array'
        ]);

        $integration = Auth::user()->integrations()->create([
            'name' => $validated['name'],
            'key' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'webhook_url' => $validated['webhook_url'],
            'access_token' => $validated['access_token'] ?? null,
            'additional_config' => $validated['additional_config'] ?? null,
            'is_active' => true
        ]);

        return response()->json([
            'integration' => $integration,
            'message' => 'Integration created successfully'
        ], 201);
    }

    public function update(Integration $integration, Request $request)
    {
        $this->authorize('update', $integration);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'webhook_url' => 'sometimes|url',
            'access_token' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
            'additional_config' => 'sometimes|array'
        ]);

        $integration->update($validated);

        return response()->json([
            'integration' => $integration,
            'message' => 'Integration updated successfully'
        ]);
    }

    public function destroy(Integration $integration)
    {
        $this->authorize('delete', $integration);

        $integration->delete();

        return response()->json([
            'message' => 'Integration removed successfully'
        ]);
    }

    public function testIntegration(Integration $integration)
    {
        $this->authorize('view', $integration);

        $testPayload = [
            'message' => 'Test integration from AI Assistant Platform',
            'timestamp' => now()->toDateTimeString()
        ];

        $result = $this->integrationService->processWebhook(
            $integration->key, 
            $testPayload
        );

        return response()->json([
            'success' => $result,
            'message' => $result 
                ? 'Integration tested successfully' 
                : 'Integration test failed'
        ]);
    }
}