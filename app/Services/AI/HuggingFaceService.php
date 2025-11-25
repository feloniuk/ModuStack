<?php

namespace App\Services\AI;

use App\Models\Provider;
use App\Core\AI\Contracts\AIProviderInterface;
use App\DTO\AIResponseDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    private const CACHE_PREFIX = 'huggingface_';
    private const CIRCUIT_BREAKER_PREFIX = 'hf_circuit_';

    public function generateResponse(array $payload): AIResponseDTO
    {
        // Проверка состояния провайдера
        if (!$this->isProviderAvailable()) {
            throw new \Exception('HuggingFace провайдер временно недоступен');
        }

        // Попытка использовать кэш
        $cacheKey = $this->generateCacheKey($payload);
        $cachedResponse = $this->getCachedResponse($cacheKey);
        
        if ($cachedResponse) {
            return $cachedResponse;
        }

        try {
            $response = $this->makeHuggingFaceRequest($payload);
            
            $aiResponse = new AIResponseDTO(
                $response['generated_text'], 
                $this->countTokens($payload['prompt']), 
                'huggingface_free'
            );

            // Кэширование ответа
            $this->cacheResponse($cacheKey, $aiResponse);

            return $aiResponse;

        } catch (\Exception $e) {
            $this->recordProviderFailure();
            Log::error('HuggingFace Request Failed', [
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function makeHuggingFaceRequest(array $payload): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.huggingface.api_key'),
            'Content-Type' => 'application/json'
        ])->post('https://api-inference.huggingface.co/models/gpt2', [
            'inputs' => $payload['prompt'],
            'parameters' => [
                'max_new_tokens' => $payload['max_tokens'] ?? 100,
                'temperature' => $payload['temperature'] ?? 0.7
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('HuggingFace API request failed');
        }

        return $response->json();
    }

    private function generateCacheKey(array $payload): string
    {
        return self::CACHE_PREFIX . md5(json_encode($payload));
    }

    private function getCachedResponse(string $cacheKey)
    {
        return Cache::get($cacheKey);
    }

    private function cacheResponse(string $cacheKey, AIResponseDTO $response)
    {
        // Кэшируем на 1 час
        Cache::put($cacheKey, $response, now()->addHour());
    }

    private function isProviderAvailable(): bool
    {
        $failureCount = Cache::get(self::CIRCUIT_BREAKER_PREFIX . 'failure_count', 0);
        return $failureCount < 5; // Максимум 5 неудачных попыток
    }

    private function recordProviderFailure()
    {
        $failureCount = Cache::increment(self::CIRCUIT_BREAKER_PREFIX . 'failure_count');
        
        if ($failureCount >= 5) {
            // Отключаем провайдера на час
            $provider = Provider::where('key', 'huggingface_free')->first();
            if ($provider) {
                $provider->update(['status' => 'degraded']);
            }

            Cache::put(
                self::CIRCUIT_BREAKER_PREFIX . 'reset_time', 
                now()->addHour(), 
                now()->addHour()
            );
        }
    }

    public function resetCircuitBreaker()
    {
        $resetTime = Cache::get(self::CIRCUIT_BREAKER_PREFIX . 'reset_time');
        
        if ($resetTime && now()->greaterThan($resetTime)) {
            Cache::forget(self::CIRCUIT_BREAKER_PREFIX . 'failure_count');
            
            $provider = Provider::where('key', 'huggingface_free')->first();
            if ($provider) {
                $provider->update(['status' => 'active']);
            }
        }
    }

    private function countTokens(string $text): int
    {
        // Простой подсчет токенов
        return str_word_count($text);
    }
}