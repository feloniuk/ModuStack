<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'key',
        'type',
        'webhook_url',
        'access_token',
        'refresh_token',
        'expires_at',
        'additional_config',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_config' => 'array',
        'expires_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}