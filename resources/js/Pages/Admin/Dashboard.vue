<template>
    <div>
      <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>
      
      <div class="grid grid-cols-3 gap-4">
        <div 
          v-for="(stat, key) in dashboardStats" 
          :key="key" 
          class="bg-white p-4 rounded shadow"
        >
          <h3 class="text-lg font-semibold">{{ stat.label }}</h3>
          <p class="text-3xl font-bold mt-2">{{ stat.value }}</p>
        </div>
      </div>
  
      <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
        <table class="w-full bg-white shadow rounded">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-3 text-left">Type</th>
              <th class="p-3 text-left">Details</th>
              <th class="p-3 text-left">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="activity in recentActivities" 
              :key="activity.id"
              class="border-b"
            >
              <td class="p-3">{{ activity.type }}</td>
              <td class="p-3">{{ activity.details }}</td>
              <td class="p-3">{{ activity.date }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        dashboardStats: {
          users: { 
            label: 'Total Users', 
            value: 0 
          },
          requests: { 
            label: 'AI Requests', 
            value: 0 
          },
          revenue: { 
            label: 'Monthly Revenue', 
            value: '$0' 
          }
        },
        recentActivities: [
          // Placeholder data - будет заменено на реальные данные
          { 
            id: 1, 
            type: 'User Signup', 
            details: 'New user registered', 
            date: '2025-01-15' 
          }
        ]
      }
    },
    async mounted() {
      try {
        const response = await axios.get('/api/admin/dashboard')
        const data = response.data
  
        // Обновление статистики
        this.dashboardStats.users.value = data.total_users
        this.dashboardStats.requests.value = data.total_ai_requests
        this.dashboardStats.revenue.value = `$${data.revenue_stats.monthly_revenue.toFixed(2)}`
  
        // Обработка недавней активности
        this.recentActivities = data.recent_activity.recent_users.map(user => ({
          id: user.id,
          type: 'User Signup',
          details: user.name,
          date: user.created_at
        }))
      } catch (error) {
        console.error('Failed to load dashboard', error)
      }
    }
  }
  </script>