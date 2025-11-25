<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AI\HuggingFaceService;
use App\Models\Provider;

class ResetHuggingFaceProvider extends Command
{
    protected $signature = 'huggingface:reset';
    protected $description = 'Reset HuggingFace provider circuit breaker';

    protected $huggingFaceService;

    public function __construct(HuggingFaceService $huggingFaceService)
    {
        parent::__construct();
        $this->huggingFaceService = $huggingFaceService;
    }

    public function handle()
    {
        try {
            $this->huggingFaceService->resetCircuitBreaker();
            
            $provider = Provider::where('key', 'huggingface_free')->first();
            if ($provider) {
                $provider->update(['status' => 'active']);
            }

            $this->info('HuggingFace provider circuit breaker reset successfully');
        } catch (\Exception $e) {
            $this->error('Failed to reset HuggingFace provider: ' . $e->getMessage());
        }
    }
}