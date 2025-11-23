<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProviderInterface;
use App\DTO\AIResponseDTO;
use App\Models\Provider;
use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseAIProvider implements AIProviderInterface
{
    public Provider $providerModel;
    protected array $config;

    public function __construct(Provider $provider)
    {
        $this->providerModel = $provider;
        $this->config = $provider->meta ?? [];
    }

    abstract public function generate(array $payload): AIResponseDTO;

    public function health(): bool 
    {
        return $this->providerModel->isActive();
    }

    public function countTokens(string $prompt): int 
    {
        return str_word_count($prompt) * 1.3;
    }

    protected function logAIRequest(array $payload, AIResponseDTO $response): void
    {
        Log::channel('ai_requests')->info('AI Request', [
            'provider' => $this->providerModel->key,
            'model' => $response->getModelUsed(),
            'tokens_used' => $response->getTokensUsed(),
            'payload' => $payload
        ]);
    }

    protected function validatePayload(array $payload): void
    {
        if (!isset($payload['prompt'])) {
            throw new Exception('Prompt is required');
        }
    }
}