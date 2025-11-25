<?php

use App\Http\Controllers\API\AssistantChatController;
use App\Http\Controllers\API\UsageController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\AssistantController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\DataExportController;
use App\Http\Controllers\API\AssistantCategoryController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ProviderManagementController;
use App\Http\Controllers\Admin\PlanManagementController;
use App\Http\Controllers\Webhooks\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'verified', 'is_admin'])->group(function () {
    Route::post('/assistants/{assistantId}/chat', [AssistantChatController::class, 'send'])
        ->name('assistants.chat');

    Route::prefix('webhooks')->group(function () {
        Route::post('/fondy', [PaymentWebhookController::class, 'handleFondy'])
            ->name('webhooks.fondy');
        Route::post('/liqpay', [PaymentWebhookController::class, 'handleLiqPay'])
            ->name('webhooks.liqpay');
    });

    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/plans/{plan}/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'cancel']);

    // Управление пользователями
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::get('/users/{user}', [UserManagementController::class, 'show']);
    Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus']);

    // Управление планами
    Route::get('/plans', [PlanManagementController::class, 'index']);
    Route::post('/plans', [PlanManagementController::class, 'store']);
    Route::put('/plans/{plan}', [PlanManagementController::class, 'update']);
    Route::delete('/plans/{plan}', [PlanManagementController::class, 'destroy']);

    // Управление провайдерами
    Route::get('/providers', [ProviderManagementController::class, 'index']);
    Route::patch('/providers/{provider}/status', [ProviderManagementController::class, 'updateStatus']);

    Route::get('/usage', [UsageController::class, 'index']);
    Route::get('/usage/{resourceType}', [UsageController::class, 'index']);

    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{project}/collaborators', [ProjectController::class, 'addCollaborator']);
    Route::delete('/projects/{project}/collaborators/{user}', [ProjectController::class, 'removeCollaborator']);
});

Route::middleware([
    'auth:sanctum', 
    'verified', 
    'prevent.abuse',
    'token.valid'
])->group(function () {
    // Ассистенты
    Route::apiResource('assistants', AssistantController::class);

    // Категории ассистентов
    Route::get('/assistant-categories', [AssistantCategoryController::class, 'index']);
    Route::post('/assistant-categories', [AssistantCategoryController::class, 'store']);

    Route::get('/analytics', [AnalyticsController::class, 'getUserAnalytics']);
    Route::get('/analytics/{period}', [AnalyticsController::class, 'getDetailedAnalytics']);
    Route::get('/export/{format?}', [DataExportController::class, 'exportUserData']);

});