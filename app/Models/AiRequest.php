<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'provider_id', 'model_name', 'prompt', 
        'response', 'tokens_used', 'cost_cents', 'status', 'meta'
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost_cents' => 'integer',
        'meta' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function markAsCompleted(string $response, int $tokensUsed)
    {
        $this->update([
            'response' => $response,
            'tokens_used' => $tokensUsed,
            'status' => 'completed'
        ]);
    }

    public function markAsFailed(string $error)
    {
        $this->update([
            'response' => $error,
            'status' => 'failed'
        ]);
    }
}