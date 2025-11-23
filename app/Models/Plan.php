<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'is_free', 'price', 'features'
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'price' => 'float',
        'features' => 'json'
    ];

    public function getRequestsPerDay(): int
    {
        return $this->features['requests_per_day'] ?? 0;
    }

    public function getMaxAssistants(): ?int
    {
        $max = $this->features['max_assistants'];
        return $max === null ? null : (int)$max;
    }

    public function getAllowedModels(): array
    {
        return $this->features['ai_models'] ?? [];
    }
}