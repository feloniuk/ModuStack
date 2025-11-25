<template>
    <div class="min-h-screen bg-gray-100 p-6">
      <h1 class="text-3xl font-bold mb-6">Административная панель</h1>
      
      <div class="grid grid-cols-4 gap-4 mb-8">
        <div 
          v-for="(stat, key) in dashboardStats" 
          :key="key" 
          class="bg-white shadow rounded-lg p-4 hover:shadow-md transition"
        >
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase">{{ stat.label }}</h3>
              <p class="text-2xl font-bold mt-2">{{ stat.value }}</p>
            </div>
            <div class="bg-blue-100 text-blue-500 p-3 rounded-full">
              <i :class="stat.icon"></i>
            </div>
          </div>
          <div class="mt-2 text-sm text-gray-500">
            {{ stat.description }}
          </div>
        </div>
      </div>
  
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Последние пользователи</h2>
          <table class="w-full">
            <thead>
              <tr class="bg-gray-100">
                <th class="p-2 text-left">Имя</th>
                <th class="p-2 text-left">Email</th>
                <th class="p-2 text-left">Дата регистрации</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="user in latestUsers" 
                :key="user.id" 
                class="border-b hover:bg-gray-50"
              >
                <td class="p-2">{{ user.name }}</td>
                <td class="p-2">{{ user.email }}</td>
                <td class="p-2">{{ formatDate(user.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Статистика AI-запросов</h2>
          <div class="space-y-4">
            <div v-for="provider in providerStats" :key="provider.name">
              <div class="flex justify-between mb-2">
                <span>{{ provider.name }}</span>
                <span>{{ provider.requests }} запросов</span>
              </div>
              <div class="bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-blue-500 rounded-full h-2" 
                  :style="`width: ${provider.percentage}%`"
                ></div>
              </div>
            </div>
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
        providerStats: []
      }
    },
    methods: {
      formatDate(date) {
        return new Date(date).toLocaleDateString()
      },
      async fetchDashboardData() {
        try {
          const response = await this.$axios.get('/admin/dashboard')
          const data = response.data
  
          this.dashboardStats.users.value = data.stats.total_users
          this.dashboardStats.aiRequests.value = data.usage_stats.total_ai_requests
          this.dashboardStats.plans.value = data.stats.total_plans
          this.dashboardStats.revenue.value = `${data.revenue_stats.monthly_revenue} ₴`
  
          this.latestUsers = data.recent_activity.recent_users
          
          this.providerStats = data.usage_stats.tokens_by_provider.map(provider => ({
            name: provider.provider_id,
            requests: provider.total_tokens,
            percentage: (provider.total_tokens / data.usage_stats.total_ai_requests) * 100
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