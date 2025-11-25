<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanManagementController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount(['subscriptions'])
            ->get()
            ->map(function ($plan) {
                $plan->active_subscriptions = $plan->subscriptions()->where('status', 'active')->count();
                return $plan;
            });

        return response()->json([
            'plans' => $plans,
            'total_plans' => $plans->count()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:plans,name',
            'slug' => 'required|unique:plans,slug',
            'price' => 'required|numeric',
            'features' => 'required|array',
            'is_free' => 'boolean'
        ]);

        $plan = Plan::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'price' => $validated['price'],
            'features' => $validated['features'],
            'is_free' => $validated['is_free'] ?? false
        ]);

        return response()->json([
            'message' => 'План создан',
            'plan' => $plan
        ], 201);
    }

    public function update(Plan $plan, Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|unique:plans,name,'.$plan->id,
            'slug' => 'sometimes|unique:plans,slug,'.$plan->id,
            'price' => 'sometimes|numeric',
            'features' => 'sometimes|array',
            'is_free' => 'sometimes|boolean'
        ]);

        $plan->update($validated);

        return response()->json([
            'message' => 'План обновлен',
            'plan' => $plan
        ]);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->json([
            'message' => 'План удален'
        ]);
    }
}