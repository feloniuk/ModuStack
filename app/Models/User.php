<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['is_admin'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function aiRequests()
    {
        return $this->hasMany(AiRequest::class);
    }

    // Добавляем метод subscriptions
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentPlan()
    {
        // Возвращаем активную подписку или первый план
        return $this->subscriptions()
            ->where('status', 'active')
            ->first()?->plan ?? Plan::first();
    }

    public function getIsAdminAttribute(): bool
    {
        // Можно использовать более сложную логику, например, 
        // роли из отдельной таблицы или специальные email
        return in_array($this->email, [
            'admin@example.com', 
            // Другие admin emails
        ]);
    }
}