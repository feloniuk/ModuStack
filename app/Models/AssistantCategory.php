<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;


class AssistantCategory extends Model
{
    protected $fillable = [
        'name', 
        'slug', 
        'description'
    ];

    public function assistants(): BelongsToMany
    {
        return $this->belongsToMany(
            Assistant::class, 
            'assistant_category_pivot', 
            'category_id', 
            'assistant_id'
        );
    }
}