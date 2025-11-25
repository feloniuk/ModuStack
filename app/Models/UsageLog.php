<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class UsageLog extends Model
{
    protected $fillable = [
        'user_id', 
        'subscription_id', 
        'resource_type', 
        'tokens_used', 
        'requests_count', 
        'metadata', 
        'log_date'
    ];

    protected $casts = [
        'metadata' => 'array',
        'log_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public static function logUsage(
        User $user, 
        string $resourceType, 
        int $tokensUsed, 
        array $metadata = []
    ): self {
        return self::updateOrCreate(
            [
                'user_id' => $user->id,
                'log_date' => now()->toDateString(),
                'resource_type' => $resourceType,
                'subscription_id' => $user->subscriptions()->active()->first()?->id
            ],
            [
                'tokens_used' => DB::raw("tokens_used + $tokensUsed"),
                'requests_count' => DB::raw('requests_count + 1'),
                'metadata' => $metadata
            ]
        );
    }
}