<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-md">
      <div class="p-4 border-b">
        <h2 class="text-xl font-bold">Admin Panel</h2>
      </div>
      <nav class="mt-4">
        <router-link 
          v-for="item in menuItems" 
          :key="item.route"
          :to="{ name: item.route }"
          class="block px-4 py-2 hover:bg-gray-100"
        >
          {{ item.label }}
        </router-link>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      menuItems: [
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Users', route: 'admin.users' },
        { label: 'Plans', route: 'admin.plans' },
        { label: 'Providers', route: 'admin.providers' },
        { label: 'Subscriptions', route: 'admin.subscriptions' },
        { label: 'AI Requests', route: 'admin.ai-requests' }
      ]
    }
  }
}
</script>