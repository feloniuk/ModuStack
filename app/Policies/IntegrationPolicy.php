<?php

namespace App\Policies;

use App\Models\Integration;
use App\Models\User;

class IntegrationPolicy
{
    public function view(User $user, Integration $integration)
    {
        return $integration->user_id === $user->id;
    }

    public function update(User $user, Integration $integration)
    {
        return $integration->user_id === $user->id;
    }

    public function delete(User $user, Integration $integration)
    {
        return $integration->user_id === $user->id;
    }
}