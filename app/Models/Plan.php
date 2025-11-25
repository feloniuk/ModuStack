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

    public function getAllowedFeatures(): array
    {
        return [
            'max_tokens_per_day' => $this->features['max_tokens_per_day'] ?? 10000,
            'max_tokens_per_month' => $this->features['max_tokens_per_month'] ?? 100000,
            'max_assistants' => $this->features['max_assistants'] ?? 1,
            'allowed_models' => $this->features['ai_models'] ?? ['huggingface_free']
        ];
    }

    public function canUseModel(string $modelKey): bool
    {
        return in_array($modelKey, $this->getAllowedFeatures()['allowed_models']);
    }
}