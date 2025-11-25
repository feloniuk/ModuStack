<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnsureTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
        // Проверка действительности токена
        if (Auth::guard('sanctum')->check()) {
            $user = $request->user();

            // Проверка последней активности
            if ($user->tokens()->where('last_used_at', '<', now()->subDays(30))->exists()) {
                // Деактивируем старые токены
                $user->tokens()->where('last_used_at', '<', now()->subDays(30))->delete();
            }

            // Проверка на подозрительную активность
            $this->checkSuspiciousActivity($user);
        }

        return $next($request);
    }

    protected function checkSuspiciousActivity($user)
    {
        // Логика обнаружения подозрительной активности
        $recentFailedLogins = DB::table('failed_logins')
            ->where('user_id', $user->id)
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        if ($recentFailedLogins > 5) {
            // Временная блокировка
            Log::warning('Suspicious login activity detected', [
                'user_id' => $user->id,
                'failed_attempts' => $recentFailedLogins
            ]);

            // Опционально: отправка уведомления пользователю
            $this->sendSecurityAlert($user);
        }
    }

    protected function sendSecurityAlert($user)
    {
        // Отправка email о подозрительной активности
        Mail::to($user->email)->send(
            new \App\Mail\SecurityAlertMail($user)
        );
    }
}