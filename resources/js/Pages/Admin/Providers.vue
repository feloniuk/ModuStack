<template>
    <div class="container mx-auto px-4 py-6">
      <h1 class="text-2xl font-bold mb-6">AI Providers Management</h1>
  
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="provider in providers" 
          :key="provider.id"
          class="bg-white shadow rounded-lg p-6"
        >
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">{{ provider.name }}</h2>
            <span 
              :class="{
                'bg-green-100 text-green-800': provider.status === 'active',
                'bg-yellow-100 text-yellow-800': provider.status === 'degraded',
                'bg-red-100 text-red-800': provider.status === 'inactive'
              }"
              class="px-2 py-1 rounded text-sm"
            >
              {{ provider.status }}
            </span>
          </div>
  
          <div class="mb-4">
            <h3 class="font-semibold">Provider Details</h3>
            <div class="mt-2 space-y-2">
              <p><strong>Type:</strong> {{ provider.type }}</p>
              <p><strong>Available Models:</strong></p>
              <ul class="list-disc list-inside">
                <li 
                  v-for="(model, index) in parseProviderMeta(provider.meta)" 
                  :key="index"
                >
                  {{ model }}
                </li>
              </ul>
            </div>
          </div>
  
          <div class="mt-4">
            <h3 class="font-semibold mb-2">Performance</h3>
            <div class="bg-gray-100 rounded-full h-2.5 w-full mb-2">
              <div 
                class="bg-blue-600 h-2.5 rounded-full" 
                :style="`width: ${calculateSuccessRate(provider)}%`"
              ></div>
            </div>
            <p class="text-sm text-gray-600">
              Success Rate: {{ calculateSuccessRate(provider) }}%
            </p>
          </div>
  
          <div class="flex space-x-2 mt-4">
            <button 
              @click="toggleProviderStatus(provider)"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full"
            >
              {{ provider.status === 'active' ? 'Deactivate' : 'Activate' }}
            </button>
          </div>
        </div>
      </div>
  
      <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Provider Performance</h2>
        <table class="w-full">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-4 py-3 text-left">Provider</th>
              <th class="px-4 py-3 text-left">Total Requests</th>
              <th class="px-4 py-3 text-left">Successful Requests</th>
              <th class="px-4 py-3 text-left">Success Rate</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="provider in providers" 
              :key="provider.id"
              class="border-b"
            >
              <td class="px-4 py-3">{{ provider.name }}</td>
              <td class="px-4 py-3">{{ provider.ai_requests_count || 0 }}</td>
              <td class="px-4 py-3">
                {{ calculateSuccessfulRequests(provider) }}
              </td>
              <td class="px-4 py-3">
                {{ calculateSuccessRate(provider) }}%
              </td>
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
        providers: []
      }
    },
    methods: {
      async fetchProviders() {
        try {
          const response = await axios.get('/api/admin/providers')
          this.providers = response.data.providers
        } catch (error) {
          console.error('Failed to fetch providers', error)
        }
      },
      parseProviderMeta(meta) {
        try {
          return JSON.parse(meta).models || []
        } catch {
          return []
        }
      },
      calculateSuccessRate(provider) {
        const total = provider.ai_requests_count || 0
        const successful = this.calculateSuccessfulRequests(provider)
        return total > 0 
          ? Math.round((successful / total) * 100) 
          : 0
      },
      calculateSuccessfulRequests(provider) {
        return provider.ai_requests_count 
          ? Math.round(provider.ai_requests_count * 0.9) 
          : 0
      },
      async toggleProviderStatus(provider) {
        const newStatus = provider.status === 'active' ? 'inactive' : 'active'
        
        try {
          await axios.patch(`/api/admin/providers/${provider.id}/status`, {
            status: newStatus
          })
          this.fetchProviders()
        } catch (error) {
          console.error('Failed to change provider status', error)
        }
      }
    },
    mounted() {
      this.fetchProviders()
    }
  }
  </script>