<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 
        'subscription_id', 
        'amount', 
        'currency',
        'gateway', 
        'transaction_id', 
        'payment_method', 
        'status', 
        'gateway_response'
    ];

    protected $casts = [
        'amount' => 'float',
        'gateway_response' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}