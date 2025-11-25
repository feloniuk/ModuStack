<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id', 
        'name', 
        'slug', 
        'description', 
        'visibility', 
        'settings', 
        'last_used_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'last_used_at' => 'datetime'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_collaborators')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function assistants(): HasMany
    {
        return $this->hasMany(Assistant::class);
    }

    public function scopeVisibleTo(Builder $query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('visibility', 'public')
              ->orWhereHas('collaborators', function ($q) use ($user) {
                  $q->where('user_id', $user->id);
              });
        });
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id || 
               $this->collaborators()
                   ->where('user_id', $user->id)
                   ->whereIn('role', ['owner', 'admin', 'editor'])
                   ->exists();
    }
}