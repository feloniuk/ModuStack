<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::visibleTo(Auth::user())
            ->with('assistants')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(20);

        return response()->json([
            'projects' => $projects,
            'total_projects' => $projects->total()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'in:private,shared,public',
            'settings' => 'nullable|array'
        ]);

        $project = Project::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'] . '-' . now()->timestamp),
            'description' => $validated['description'] ?? null,
            'visibility' => $validated['visibility'] ?? 'private',
            'settings' => $validated['settings'] ?? []
        ]);

        return response()->json([
            'project' => $project,
            'message' => 'Проект создан успешно'
        ], 201);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['assistants', 'collaborators']);

        return response()->json([
            'project' => $project,
            'can_edit' => $project->canBeEditedBy(Auth::user())
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'in:private,shared,public',
            'settings' => 'nullable|array'
        ]);

        $project->update(array_filter($validated));

        return response()->json([
            'project' => $project,
            'message' => 'Проект обновлен успешно'
        ]);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json([
            'message' => 'Проект удален успешно'
        ]);
    }

    public function addCollaborator(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,editor,viewer'
        ]);

        $user = User::where('email', $validated['email'])->first();

        $project->collaborators()->attach($user, [
            'role' => $validated['role']
        ]);

        return response()->json([
            'message' => 'Участник добавлен в проект',
            'collaborator' => $user
        ]);
    }

    public function removeCollaborator(Project $project, User $user)
    {
        $this->authorize('update', $project);

        $project->collaborators()->detach($user);

        return response()->json([
            'message' => 'Участник удален из проекта'
        ]);
    }
}