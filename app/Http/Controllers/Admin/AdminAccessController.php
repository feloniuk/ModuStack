<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAccessController extends Controller
{
    public function checkAdminAccess(Request $request)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have administrator privileges'
            ], 403);
        }

        return response()->json([
            'admin_access' => true,
            'user' => $user
        ]);
    }

    private function isAdmin($user): bool
    {
        // Здесь может быть более сложная логика проверки прав
        return in_array($user->email, [
            'admin@example.com',
            // Добавьте email администраторов
        ]);
    }
}