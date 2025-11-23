<?php

namespace App\Actions\AI;

use App\Models\User;
use App\Services\AIService;
use App\DTO\AIResponseDTO;

class GenerateAIResponseAction
{
    private AIService $aiService;

    public function __construct(?AIService $aiService = null)
    {
        $this->aiService = $aiService ?? new AIService();
    }

    public function __invoke(User $user, array $payload): AIResponseDTO
    {
        $aiRequest = $this->aiService->processRequest($user, $payload);
        
        return new AIResponseDTO(
            $aiRequest->response, 
            $aiRequest->tokens_used, 
            $aiRequest->model_name
        );
    }
}