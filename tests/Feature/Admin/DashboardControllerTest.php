<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_statistics()
    {
        // Создаем администратора
        $admin = User::factory()->create(['is_admin' => true]);

        // Аутентифицируемся как администратор
        $this->actingAs($admin);

        // Запрос к административной статистике
        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_users',
                'total_active_users', 
                'total_plans',
                'total_providers',
                'total_subscriptions',
                'total_ai_requests',
                'revenue_stats' => [
                    'total_revenue',
                    'monthly_revenue',
                    'revenue_by_plan'
                ],
                'usage_stats' => [
                    'total_tokens_used',
                    'monthly_tokens',
                    'tokens_by_provider'
                ],
                'recent_activity' => [
                    'recent_users',
                    'recent_ai_requests',
                    'recent_subscriptions',
                    'recent_payments'
                ]
            ]);
    }

    public function test_non_admin_cannot_access_dashboard()
    {
        // Создаем обычного пользователя
        $user = User::factory()->create(['is_admin' => false]);

        // Аутентифицируемся как обычный пользователь
        $this->actingAs($user);

        // Запрос к административной статистике
        $response = $this->getJson('/api/admin/dashboard');

        // Должны получить ошибку доступа
        $response->assertStatus(403);
    }
}