<template>
    <div class="min-h-screen bg-gray-100 p-6">
      <h1 class="text-3xl font-bold mb-6">Административная панель</h1>
      
      <div class="grid grid-cols-4 gap-4">
        <div 
          v-for="(stat, key) in dashboardStats" 
          :key="key" 
          class="bg-white shadow rounded-lg p-4"
        >
          <h3 class="text-lg font-semibold">{{ stat.label }}</h3>
          <p class="text-2xl font-bold mt-2">{{ stat.value }}</p>
        </div>
      </div>
  
      <div class="mt-8 grid grid-cols-2 gap-4">
        <div class="bg-white shadow rounded-lg p-4">
          <h2 class="text-xl font-semibold mb-4">Недавняя активность</h2>
          <!-- Placeholder for activity log -->
          <p>Активность будет добавлена</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
          <h2 class="text-xl font-semibold mb-4">Статистика</h2>
          <!-- Placeholder for stats -->
          <p>Статистика будет добавлена</p>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        dashboardStats: {
          users: { label: 'Пользователи', value: '-' },
          requests: { label: 'AI Запросы', value: '-' },
          plans: { label: 'Тарифы', value: '-' },
          providers: { label: 'Провайдеры', value: '-' }
        }
      }
    },
    mounted() {
      this.fetchDashboardStats()
    },
    methods: {
      async fetchDashboardStats() {
        try {
          const response = await axios.get('/api/admin/dashboard')
          const data = response.data
  
          this.dashboardStats.users.value = data.stats.total_users
          this.dashboardStats.requests.value = data.usage_stats.total_ai_requests
          this.dashboardStats.plans.value = data.stats.total_plans
          this.dashboardStats.providers.value = data.stats.total_providers
        } catch (error) {
          console.error('Ошибка загрузки статистики', error)
        }
      }
    }
  }
  </script>