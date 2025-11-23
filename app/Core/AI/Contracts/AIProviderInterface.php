<?php

namespace App\Core\AI\Contracts;

use App\DTO\AIResponseDTO;

interface AIProviderInterface 
{
    public function generate(array $payload): AIResponseDTO;
    public function health(): bool;
    public function countTokens(string $prompt): int;
}