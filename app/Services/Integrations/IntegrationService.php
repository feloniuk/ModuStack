<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntegrationService
{
    protected $integrations = [];

    public function __construct()
    {
        $this->loadIntegrations();
    }

    protected function loadIntegrations()
    {
        // Загрузка активных интеграций из базы данных
        $this->integrations = \App\Models\Integration::active()->get();
    }

    public function processWebhook(string $integrationKey, array $payload)
    {
        $integration = $this->findIntegration($integrationKey);
        
        if (!$integration) {
            Log::warning("Integration not found: {$integrationKey}");
            return false;
        }

        return $this->executeIntegration($integration, $payload);
    }

    protected function findIntegration(string $key)
    {
        return $this->integrations->firstWhere('key', $key);
    }

    protected function executeIntegration($integration, array $payload)
    {
        try {
            // Базовая логика выполнения интеграции
            $method = $this->resolveIntegrationMethod($integration->type);
            return $this->$method($integration, $payload);
        } catch (\Exception $e) {
            Log::error("Integration error: {$integration->key}", [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    protected function resolveIntegrationMethod(string $type)
    {
        $methodMap = [
            'slack' => 'processSlackIntegration',
            'discord' => 'processDiscordIntegration',
            'webhook' => 'processGenericWebhook',
            // Другие типы интеграций
        ];

        return $methodMap[$type] ?? 'processGenericWebhook';
    }

    protected function processSlackIntegration($integration, $payload)
    {
        $response = Http::withToken($integration->access_token)
            ->post($integration->webhook_url, [
                'text' => $payload['message'] ?? 'Новое событие',
                'channel' => $integration->channel
            ]);

        return $response->successful();
    }

    protected function processDiscordIntegration($integration, $payload)
    {
        $response = Http::post($integration->webhook_url, [
            'content' => $payload['message'] ?? 'Новое событие',
            'embeds' => $payload['embeds'] ?? []
        ]);

        return $response->successful();
    }

    protected function processGenericWebhook($integration, $payload)
    {
        $response = Http::withHeaders($integration->headers ?? [])
            ->post($integration->webhook_url, $payload);

        return $response->successful();
    }
}