<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project)
    {
        return $project->user_id === $user->id || 
               $project->visibility === 'public' ||
               $project->collaborators()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Project $project)
    {
        return $project->user_id === $user->id || 
               $project->collaborators()
                   ->where('user_id', $user->id)
                   ->whereIn('role', ['owner', 'admin'])
                   ->exists();
    }

    public function delete(User $user, Project $project)
    {
        return $project->user_id === $user->id;
    }
}