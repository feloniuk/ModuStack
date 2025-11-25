<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class PreventAbuse
{
    public function handle(Request $request, Closure $next)
    {
        // IP-based rate limiting
        $key = 'rate_limit:' . $request->ip();
        
        // Максимум 100 запросов в минуту для неавторизованных
        if (RateLimiter::tooManyAttempts($key, 100)) {
            return response()->json([
                'message' => 'Too many requests. Please slow down.',
                'retry_after' => RateLimiter::availableIn($key)
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, 60); // 1 минута

        // Защита от потенциально опасных промптов
        $this->sanitizeInput($request);

        return $next($request);
    }

    protected function sanitizeInput(Request $request)
    {
        // Список запрещенных команд и паттернов
        $dangerousPatterns = [
            '/\b(rm\s+-rf|sudo|wget|curl)\b/i', // Системные команды
            '/\$\{.*\}/i', // Шелл-инъекции
            '/(?:select|union|insert|update|delete|drop)\s+.*\bfrom\b/i', // SQL-инъекции
            '/(<\?php|eval\(|system\(|exec\()/i', // Выполнение кода
        ];

        // Проверяем все строковые параметры запроса
        $request->merge(
            collect($request->all())->map(function ($value) use ($dangerousPatterns) {
                if (is_string($value)) {
                    foreach ($dangerousPatterns as $pattern) {
                        if (preg_match($pattern, $value)) {
                            // Логируем попытку атаки
                            Log::warning('Potential security threat detected', [
                                'ip' => request()->ip(),
                                'pattern' => $pattern,
                                'value' => $value
                            ]);
                            return preg_replace($pattern, '[SANITIZED]', $value);
                        }
                    }
                }
                return $value;
            })->toArray()
        );
    }
}