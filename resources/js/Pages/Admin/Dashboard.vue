<template>
    <div class="min-h-screen bg-gray-100 p-6">
      <div class="container mx-auto">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-3xl font-bold">Административная панель</h1>
          <div class="flex space-x-4">
            <router-link 
              v-if="$page.props.auth.user.is_admin"
              :to="{ name: 'admin.users' }"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition"
            >
              Управление пользователями
            </router-link>
          </div>
        </div>
  
        <!-- Статистика -->
        <div class="grid grid-cols-4 gap-6 mb-8">
          <div 
            v-for="(stat, key) in dashboardStats" 
            :key="key" 
            class="bg-white shadow-md rounded-lg p-6 transform transition hover:scale-105"
          >
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">
                  {{ stat.label }}
                </h3>
                <p class="text-3xl font-bold">{{ stat.value }}</p>
              </div>
              <div 
                class="bg-blue-100 text-blue-500 p-3 rounded-full"
              >
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="mt-2 text-sm text-gray-500">
              {{ stat.description }}
            </div>
          </div>
        </div>
  
        <!-- Графики и детальная статистика -->
        <div class="grid grid-cols-2 gap-6">
          <!-- Последние пользователи -->
          <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Последние пользователи</h2>
            <div v-if="latestUsers.length" class="space-y-3">
              <div 
                v-for="user in latestUsers" 
                :key="user.id" 
                class="flex justify-between items-center border-b pb-2 last:border-b-0"
              >
                <div>
                  <p class="font-medium">{{ user.name }}</p>
                  <p class="text-sm text-gray-500">{{ user.email }}</p>
                </div>
                <span class="text-sm text-gray-500">
                  {{ formatRelativeDate(user.created_at) }}
                </span>
              </div>
            </div>
            <p v-else class="text-center text-gray-500">Нет новых пользователей</p>
          </div>
  
          <!-- AI-запросы -->
          <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Статистика AI-запросов</h2>
            <div v-if="aiRequestStats.length" class="space-y-3">
              <div 
                v-for="(stat, index) in aiRequestStats" 
                :key="index" 
                class="flex items-center"
              >
                <div class="flex-grow">
                  <p class="font-medium">{{ stat.provider }}</p>
                  <div class="bg-gray-200 h-2 rounded-full overflow-hidden mt-1">
                    <div 
                      class="bg-blue-500 h-2 rounded-full" 
                      :style="`width: ${stat.percentage}%`"
                    ></div>
                  </div>
                </div>
                <span class="ml-4 text-sm text-gray-500">
                  {{ stat.requests }} запросов
                </span>
              </div>
            </div>
            <p v-else class="text-center text-gray-500">Нет данных о запросах</p>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        dashboardStats: {
          users: { 
            label: 'Пользователи', 
            value: 0, 
            icon: 'fas fa-users',
            description: 'Всего зарегистрированных'
          },
          aiRequests: { 
            label: 'AI Запросы', 
            value: 0, 
            icon: 'fas fa-robot',
            description: 'Запросов за месяц'
          },
          plans: { 
            label: 'Тарифы', 
            value: 0, 
            icon: 'fas fa-layer-group',
            description: 'Активных подписок'
          },
          revenue: { 
            label: 'Выручка', 
            value: '0 ₴', 
            icon: 'fas fa-chart-line',
            description: 'За последний месяц'
          }
        },
        latestUsers: [],
        aiRequestStats: []
      }
    },
    methods: {
      formatRelativeDate(date) {
        const diff = new Date() - new Date(date)
        const days = Math.floor(diff / (1000 * 60 * 60 * 24))
        return days === 0 ? 'Сегодня' : `${days} дн. назад`
      },
      async fetchDashboardData() {
        try {
          const response = await this.$axios.get('/admin/dashboard')
          const data = response.data
  
          // Обновляем статистику
          this.dashboardStats.users.value = data.stats.total_users
          this.dashboardStats.aiRequests.value = data.usage_stats.total_ai_requests
          this.dashboardStats.plans.value = data.stats.total_plans
          this.dashboardStats.revenue.value = `${data.revenue_stats.monthly_revenue} ₴`
  
          // Последние пользователи
          this.latestUsers = data.recent_activity.recent_users.slice(0, 5)
  
          // Статистика AI-запросов
          const totalRequests = data.usage_stats.total_ai_requests
          this.aiRequestStats = data.usage_stats.tokens_by_provider.map(provider => ({
            provider: provider.provider_id,
            requests: provider.total_tokens,
            percentage: Math.round((provider.total_tokens / totalRequests) * 100)
          }))
        } catch (error) {
          this.$toast.error('Не удалось загрузить статистику')
        }
      }
    },
    mounted() {
      this.fetchDashboardData()
    }
  }
  </script>