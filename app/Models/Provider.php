<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'name', 'type', 'status', 'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'status' => 'string'
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}