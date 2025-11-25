<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssistantCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssistantCategoryController extends Controller
{
    public function index()
    {
        $categories = AssistantCategory::withCount('assistants')->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:assistant_categories,name',
            'description' => 'nullable|string'
        ]);

        $category = AssistantCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null
        ]);

        return response()->json([
            'category' => $category,
            'message' => 'Категория создана успешно'
        ], 201);
    }
}