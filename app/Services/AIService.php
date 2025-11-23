<?php

namespace App\Services;

use App\Core\AI\AIProviderFactory;
use App\Core\AI\Contracts\AIProviderInterface;
use App\DTO\AIResponseDTO;
use App\Models\AiRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class AIService 
{
    private AIProviderInterface $provider;

    public function __construct(?AIProviderInterface $provider = null) 
    {
        $this->provider = $provider ?? AIProviderFactory::getDefaultProvider();
    }

    public function processRequest(User $user, array $payload): AiRequest 
    {
        try {
            $this->checkUserLimits($user, $payload);

            $aiRequest = AiRequest::create([
                'user_id' => $user->id,
                'provider_id' => $this->provider->providerModel->id,
                'model_name' => $payload['model'] ?? 'default',
                'prompt' => $payload['prompt'],
                'status' => 'processing'
            ]);

            $response = $this->provider->generate($payload);

            $aiRequest->markAsCompleted($response->getText(), $response->getTokensUsed());

            return $aiRequest;

        } catch (Exception $e) {
            Log::error('AI Request Failed: ' . $e->getMessage());
            
            $aiRequest->markAsFailed($e->getMessage());
            
            throw $e;
        }
    }

    private function checkUserLimits(User $user, array $payload): void 
    {
        $plan = $user->currentPlan();
        $requestTokens = $this->provider->countTokens($payload['prompt']);

        if ($requestTokens > ($plan->features['max_tokens_per_request'] ?? 1000)) {
            throw new Exception('Превышен лимит токенов для вашего тарифного плана');
        }
    }

    public function switchProvider(string $providerKey): self 
    {
        $provider = AIProviderFactory::findBestProvider(['key' => $providerKey]);
        $this->provider = $provider;
        return $this;
    }
}