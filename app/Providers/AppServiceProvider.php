<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\ResetHuggingFaceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command(ResetHuggingFaceProvider::class)->hourly();
        });
    }

    public function register(): void
    {
        // Регистрация команды
        $this->app->bind(ResetHuggingFaceProvider::class);
    }
}