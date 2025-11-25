<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProviderInterface;
use App\DTO\AIResponseDTO;
use App\Models\Provider;
use App\Services\AI\HuggingFaceService;
use Illuminate\Support\Facades\Log;
use Exception;

class HuggingFaceFreeProvider implements AIProviderInterface
{
    private Provider $providerModel;
    private HuggingFaceService $huggingFaceService;

    public function __construct(Provider $provider, HuggingFaceService $huggingFaceService)
    {
        $this->providerModel = $provider;
        $this->huggingFaceService = $huggingFaceService;
    }

    public function generate(array $payload): AIResponseDTO 
    {
        try {
            return $this->huggingFaceService->generateResponse($payload);
        } catch (Exception $e) {
            // Логирование ошибок
            Log::error('HuggingFace generation failed', [
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function health(): bool 
    {
        return $this->providerModel->isActive();
    }

    public function countTokens(string $prompt): int 
    {
        // Простой подсчет токенов
        return str_word_count($prompt);
    }
}