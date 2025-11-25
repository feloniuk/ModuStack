<template>
    <div class="container mx-auto px-4 py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Subscriptions Management</h1>
        <div class="flex space-x-2">
          <select 
            v-model="statusFilter" 
            class="border px-3 py-2 rounded"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="canceled">Canceled</option>
            <option value="expired">Expired</option>
          </select>
          <input 
            type="text" 
            v-model="searchQuery"
            placeholder="Search subscriptions..."
            class="border px-3 py-2 rounded w-64"
          >
        </div>
      </div>
  
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-3 text-left">ID</th>
              <th class="px-4 py-3 text-left">User</th>
              <th class="px-4 py-3 text-left">Plan</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Start Date</th>
              <th class="px-4 py-3 text-left">End Date</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="subscription in filteredSubscriptions" 
              :key="subscription.id"
              class="border-b hover:bg-gray-50"
            >
              <td class="px-4 py-3">{{ subscription.id }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center">
                  <div class="ml-2">
                    <div class="text-sm font-medium">{{ subscription.user.name }}</div>
                    <div class="text-sm text-gray-500">{{ subscription.user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                <span 
                  :class="{
                    'bg-green-100 text-green-800': subscription.plan.is_free,
                    'bg-blue-100 text-blue-800': !subscription.plan.is_free
                  }"
                  class="px-2 py-1 rounded text-sm"
                >
                  {{ subscription.plan.name }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span 
                  :class="{
                    'text-green-600': subscription.status === 'active',
                    'text-red-600': subscription.status === 'canceled',
                    'text-yellow-600': subscription.status === 'expired'
                  }"
                >
                  {{ subscription.status }}
                </span>
              </td>
              <td class="px-4 py-3">{{ formatDate(subscription.starts_at) }}</td>
              <td class="px-4 py-3">{{ formatDate(subscription.ends_at) }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end space-x-2">
                  <button 
                    @click="viewSubscriptionDetails(subscription)"
                    class="text-blue-500 hover:text-blue-700"
                  >
                    Details
                  </button>
                  <button 
                    v-if="subscription.status === 'active'"
                    @click="cancelSubscription(subscription)"
                    class="text-red-500 hover:text-red-700"
                  >
                    Cancel
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
  
        <!-- Pagination -->
        <div class="px-4 py-3 bg-gray-50 flex justify-between items-center">
          <span>Total Subscriptions: {{ subscriptions.length }}</span>
          <div class="space-x-2">
            <button 
              :disabled="currentPage === 1"
              @click="currentPage--"
              class="px-3 py-1 border rounded disabled:opacity-50"
            >
              Prev
            </button>
            <button 
              :disabled="currentPage * pageSize >= subscriptions.length"
              @click="currentPage++"
              class="px-3 py-1 border rounded disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
  
      <!-- Модальное окно деталей подписки -->
      <div 
        v-if="selectedSubscription" 
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
      >
        <div class="bg-white p-6 rounded-lg w-96">
          <h2 class="text-xl font-bold mb-4">Subscription Details</h2>
          <div class="space-y-2">
            <p><strong>User:</strong> {{ selectedSubscription.user.name }}</p>
            <p><strong>Email:</strong> {{ selectedSubscription.user.email }}</p>
            <p><strong>Plan:</strong> {{ selectedSubscription.plan.name }}</p>
            <p><strong>Status:</strong> {{ selectedSubscription.status }}</p>
            <p><strong>Start Date:</strong> {{ formatDate(selectedSubscription.starts_at) }}</p>
            <p><strong>End Date:</strong> {{ formatDate(selectedSubscription.ends_at) }}</p>
          </div>
          <div class="mt-4 flex justify-end">
            <button 
              @click="selectedSubscription = null"
              class="px-4 py-2 bg-gray-200 rounded"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        subscriptions: [],
        selectedSubscription: null,
        searchQuery: '',
        statusFilter: '',
        currentPage: 1,
        pageSize: 10
      }
    },
    computed: {
      filteredSubscriptions() {
        return this.subscriptions.filter(subscription => {
          const matchesSearch = 
            subscription.user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            subscription.user.email.toLowerCase().includes(this.searchQuery.toLowerCase())
          
          const matchesStatus = 
            !this.statusFilter || 
            subscription.status === this.statusFilter
  
          return matchesSearch && matchesStatus
        }).slice((this.currentPage - 1) * this.pageSize, this.currentPage * this.pageSize)
      }
    },
    methods: {
      async fetchSubscriptions() {
        try {
          const response = await axios.get('/api/admin/subscriptions')
          this.subscriptions = response.data.subscriptions
        } catch (error) {
          console.error('Failed to fetch subscriptions', error)
        }
      },
      formatDate(date) {
        return date 
          ? new Date(date).toLocaleDateString() 
          : 'N/A'
      },
      viewSubscriptionDetails(subscription) {
        this.selectedSubscription = subscription
      },
      async cancelSubscription(subscription) {
        if (confirm(`Are you sure you want to cancel subscription for ${subscription.user.name}?`)) {
          try {
            await axios.delete(`/api/admin/subscriptions/${subscription.id}`)
            this.fetchSubscriptions()
          } catch (error) {
            console.error('Failed to cancel subscription', error)
          }
        }
      }
    },
    mounted() {
      this.fetchSubscriptions()
    }
  }
  </script>