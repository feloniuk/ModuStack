<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedLogin extends Model
{
    protected $fillable = [
        'email', 
        'ip_address'
    ];

    public $timestamps = false;

    // Статический метод для логирования неудачных попыток входа
    public static function record(string $email, ?string $ipAddress = null): self
    {
        return self::create([
            'email' => $email,
            'ip_address' => $ipAddress ?? request()->ip()
        ]);
    }
}