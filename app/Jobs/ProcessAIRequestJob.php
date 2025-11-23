<?php

namespace App\Jobs;

use App\Models\AiRequest;
use App\Core\AI\AIProviderFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAIRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected AiRequest $aiRequest;

    public function __construct(AiRequest $aiRequest)
    {
        $this->aiRequest = $aiRequest;
    }

    public function handle()
    {
        $provider = AIProviderFactory::make($this->aiRequest->provider);

        try {
            $response = $provider->generate([
                'prompt' => $this->aiRequest->prompt,
                'model' => $this->aiRequest->model_name
            ]);

            $this->aiRequest->markAsCompleted(
                $response->getText(), 
                $response->getTokensUsed()
            );
        } catch (\Exception $e) {
            $this->aiRequest->markAsFailed($e->getMessage());
        }
    }

    public function failed(\Exception $exception)
    {
        $this->aiRequest->markAsFailed($exception->getMessage());
    }
}