<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProviderInterface;
use App\DTO\AIResponseDTO;
use App\Models\Provider;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider extends BaseAIProvider
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';

    public function __construct(Provider $provider)
    {
        parent::__construct($provider);
    }

    public function generate(array $payload): AIResponseDTO 
    {
        $this->validatePayload($payload);

        try {
            $model = $payload['model'] ?? 'gpt-3.5-turbo';
            $response = Http::withToken(config('services.openai.api_key'))
                ->post(self::API_URL, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $payload['prompt']]
                    ],
                    'max_tokens' => $payload['max_tokens'] ?? 150
                ]);

            if (!$response->successful()) {
                throw new Exception('OpenAI API Error: ' . $response->body());
            }

            $responseData = $response->json();
            $generatedText = $responseData['choices'][0]['message']['content'] ?? '';
            $tokensUsed = $responseData['usage']['total_tokens'] ?? 0;

            $aiResponse = new AIResponseDTO(
                $generatedText, 
                $tokensUsed, 
                $model, 
                $responseData['usage'] ?? []
            );

            $this->logAIRequest($payload, $aiResponse);

            return $aiResponse;

        } catch (Exception $e) {
            Log::error('OpenAI Request Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function countTokens(string $prompt): int 
    {
        return (int)(strlen($prompt) / 4);
    }
}