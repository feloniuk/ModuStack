<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assistant;
use App\Models\AssistantCategory;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AssistantController extends Controller
{
    public function index(Request $request)
    {
        $assistants = Assistant::visibleTo(Auth::user())
            ->with(['project', 'categories'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->input('project_id'), function ($query, $projectId) {
                $query->where('project_id', $projectId);
            })
            ->when($request->input('category'), function ($query, $categorySlug) {
                $query->whereHas('categories', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->paginate(20);

        return response()->json([
            'assistants' => $assistants,
            'total_assistants' => $assistants->total()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'model' => 'required|in:gpt-3.5-turbo,gpt-4,gpt-4o,huggingface_free',
            'system_prompt' => 'nullable|string',
            'response_template' => 'nullable|array',
            'max_tokens' => 'integer|min:50|max:4096',
            'temperature' => 'numeric|min:0|max:2',
            'top_p' => 'numeric|min:0|max:1',
            'context_settings' => 'nullable|array',
            'is_public' => 'boolean',
            'categories' => 'nullable|array|exists:assistant_categories,id'
        ]);

        // Проверка доступа к проекту
        if (isset($validated['project_id'])) {
            $project = Project::findOrFail($validated['project_id']);
            $this->authorize('update', $project);
        }

        $assistant = Assistant::create([
            'user_id' => Auth::id(),
            'project_id' => $validated['project_id'] ?? null,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'] . '-' . now()->timestamp),
            'description' => $validated['description'] ?? null,
            'model' => $validated['model'],
            'system_prompt' => $validated['system_prompt'] ?? null,
            'response_template' => $validated['response_template'] ?? null,
            'max_tokens' => $validated['max_tokens'] ?? 1000,
            'temperature' => $validated['temperature'] ?? 0.7,
            'top_p' => $validated['top_p'] ?? 1.0,
            'context_settings' => $validated['context_settings'] ?? null,
            'is_public' => $validated['is_public'] ?? false
        ]);

        // Привязываем категории
        if (isset($validated['categories'])) {
            $assistant->categories()->sync($validated['categories']);
        }

        return response()->json([
            'assistant' => $assistant->load('categories'),
            'message' => 'Ассистент создан успешно'
        ], 201);
    }

    public function show(Assistant $assistant)
    {
        $this->authorize('view', $assistant);

        $assistant->load(['project', 'categories']);

        return response()->json([
            'assistant' => $assistant,
            'can_edit' => $assistant->canBeEditedBy(Auth::user())
        ]);
    }

    public function update(Request $request, Assistant $assistant)
    {
        $this->authorize('update', $assistant);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'model' => 'required|in:gpt-3.5-turbo,gpt-4,gpt-4o,huggingface_free',
            'system_prompt' => 'nullable|string',
            'response_template' => 'nullable|array',
            'max_tokens' => 'integer|min:50|max:4096',
            'temperature' => 'numeric|min:0|max:2',
            'top_p' => 'numeric|min:0|max:1',
            'context_settings' => 'nullable|array',
            'is_public' => 'boolean',
            'categories' => 'nullable|array|exists:assistant_categories,id'
        ]);

        $assistant->update(array_filter($validated));

        // Обновляем категории, если переданы
        if (isset($validated['categories'])) {
            $assistant->categories()->sync($validated['categories']);
        }

        return response()->json([
            'assistant' => $assistant->load('categories'),
            'message' => 'Ассистент обновлен успешно'
        ]);
    }

    public function destroy(Assistant $assistant)
    {
        $this->authorize('delete', $assistant);

        $assistant->delete();

        return response()->json([
            'message' => 'Ассистент удален успешно'
        ]);
    }
}