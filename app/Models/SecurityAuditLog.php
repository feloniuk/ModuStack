<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAuditLog extends Model
{
    protected $fillable = [
        'user_id', 
        'event_type', 
        'ip_address', 
        'user_agent', 
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Статические методы для удобства логирования
    public static function log(
        User $user, 
        string $eventType, 
        array $metadata = []
    ): self {
        return self::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata
        ]);
    }
}