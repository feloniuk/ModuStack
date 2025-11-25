<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Assistant extends Model
{
    protected $fillable = [
        'user_id', 
        'project_id', 
        'name', 
        'slug', 
        'description',
        'model',
        'system_prompt',
        'response_template',
        'max_tokens',
        'temperature',
        'top_p',
        'context_settings',
        'additional_params',
        'is_active',
        'is_public'
    ];

    protected $casts = [
        'response_template' => 'array',
        'context_settings' => 'array',
        'additional_params' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'temperature' => 'float',
        'top_p' => 'float'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            AssistantCategory::class, 
            'assistant_category_pivot', 
            'assistant_id', 
            'category_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('is_public', true)
              ->orWhereHas('project', function ($projectQuery) use ($user) {
                  $projectQuery->visibleTo($user);
              });
        });
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id || 
               $this->project?->canBeEditedBy($user) ?? false;
    }
}
