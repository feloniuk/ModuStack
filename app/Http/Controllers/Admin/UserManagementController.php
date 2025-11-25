<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UsageService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    private UsageService $usageService;

    public function __construct(UsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function index(Request $request)
    {
        $users = User::with(['subscriptions.plan', 'aiRequests'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(25);

        return response()->json([
            'users' => $users,
            'total_users' => User::count(),
            'active_users' => User::whereHas('subscriptions', function ($query) {
                $query->where('status', 'active');
            })->count()
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'subscriptions.plan', 
            'aiRequests'
        ]);

        return response()->json([
            'user' => $user,
            'usage_statistics' => $this->usageService->getUserUsageStatistics($user)
        ]);
    }

    public function updateStatus(User $user, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,suspended,banned'
        ]);

        $user->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'message' => 'Статус пользователя обновлен',
            'user' => $user
        ]);
    }
}