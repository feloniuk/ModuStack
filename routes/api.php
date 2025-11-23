<?php

use App\Http\Controllers\API\AssistantChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/assistants/{assistantId}/chat', [AssistantChatController::class, 'send'])
        ->name('assistants.chat');
});