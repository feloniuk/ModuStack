<?php

namespace App\DTO;

class AIResponseDTO 
{
    private string $text;
    private int $tokensUsed;
    private ?string $modelUsed;
    private array $metadata;

    public function __construct(
        string $text, 
        int $tokensUsed, 
        ?string $modelUsed = null, 
        array $metadata = []
    ) {
        $this->text = $text;
        $this->tokensUsed = $tokensUsed;
        $this->modelUsed = $modelUsed;
        $this->metadata = $metadata;
    }

    public function getText(): string 
    {
        return $this->text;
    }

    public function getTokensUsed(): int 
    {
        return $this->tokensUsed;
    }

    public function getModelUsed(): ?string 
    {
        return $this->modelUsed;
    }

    public function getMetadata(): array 
    {
        return $this->metadata;
    }

    public function toArray(): array 
    {
        return [
            'text' => $this->text,
            'tokens_used' => $this->tokensUsed,
            'model_used' => $this->modelUsed,
            'metadata' => $this->metadata
        ];
    }
}