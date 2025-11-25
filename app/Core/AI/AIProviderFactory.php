<?php

namespace App\Core\AI;

use App\Core\AI\Contracts\AIProviderInterface;
use App\Core\AI\Providers\OpenAIProvider;
use App\Core\AI\Providers\HuggingFaceFreeProvider;
use App\Models\Provider;
use InvalidArgumentException;

class AIProviderFactory 
{
    public static function make(Provider $provider): AIProviderInterface 
    {
        return match($provider->type) {
            'openai' => app(OpenAIProvider::class, ['provider' => $provider]),
            'huggingface' => app(HuggingFaceFreeProvider::class, ['provider' => $provider]),
            default => throw new InvalidArgumentException("Unsupported AI provider: {$provider->type}")
        };
    }

    public static function getDefaultProvider(): AIProviderInterface 
    {
        $defaultProvider = Provider::where('key', config('ai.default'))->first();
        
        if (!$defaultProvider) {
            throw new InvalidArgumentException("No default AI provider configured");
        }

        return self::make($defaultProvider);
    }

    public static function findBestProvider(array $requirements = []): AIProviderInterface 
    {
        $providers = Provider::where('status', 'active')->get();

        foreach (config('ai.fallback_order') as $providerKey) {
            $provider = $providers->firstWhere('key', $providerKey);
            
            if ($provider) {
                return self::make($provider);
            }
        }

        throw new InvalidArgumentException("No available AI providers found");
    }
}