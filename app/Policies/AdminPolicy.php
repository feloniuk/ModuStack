<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    public function viewDashboard(User $user): bool
    {
        return $user->is_admin;
    }

    public function manageUsers(User $user): bool
    {
        return $user->is_admin;
    }

    public function managePlans(User $user): bool
    {
        return $user->is_admin;
    }

    public function manageProviders(User $user): bool
    {
        return $user->is_admin;
    }
}