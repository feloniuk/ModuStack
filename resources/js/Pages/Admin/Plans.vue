<template>
    <div class="container mx-auto px-4 py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">User Management</h1>
        <button 
          @click="showCreateUserModal = true"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Create User
        </button>
      </div>
  
      <!-- Фильтры и поиск -->
      <div class="mb-4 flex space-x-4">
        <input 
          type="text" 
          v-model="searchQuery"
          placeholder="Search users..."
          class="border px-3 py-2 rounded w-full"
        >
        <select v-model="statusFilter" class="border px-3 py-2 rounded">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
          <option value="banned">Banned</option>
        </select>
      </div>
  
      <!-- Таблица пользователей -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-3 text-left">ID</th>
              <th class="px-4 py-3 text-left">Name</th>
              <th class="px-4 py-3 text-left">Email</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Registered</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="user in filteredUsers" 
              :key="user.id"
              class="border-b hover:bg-gray-50"
            >
              <td class="px-4 py-3">{{ user.id }}</td>
              <td class="px-4 py-3">{{ user.name }}</td>
              <td class="px-4 py-3">{{ user.email }}</td>
              <td class="px-4 py-3">
                <span 
                  :class="{
                    'text-green-600': user.status === 'active',
                    'text-red-600': user.status === 'banned',
                    'text-yellow-600': user.status === 'suspended'
                  }"
                >
                  {{ user.status }}
                </span>
              </td>
              <td class="px-4 py-3">{{ formatDate(user.created_at) }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end space-x-2">
                  <button 
                    @click="editUser(user)"
                    class="text-blue-500 hover:text-blue-700"
                  >
                    Edit
                  </button>
                  <button 
                    @click="changeUserStatus(user)"
                    :class="{
                      'text-red-500 hover:text-red-700': user.status === 'active',
                      'text-green-500 hover:text-green-700': user.status !== 'active'
                    }"
                  >
                    {{ user.status === 'active' ? 'Suspend' : 'Activate' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
  
        <!-- Pagination -->
        <div class="px-4 py-3 bg-gray-50 flex justify-between items-center">
          <span>Total Users: {{ users.length }}</span>
          <div class="space-x-2">
            <button 
              :disabled="currentPage === 1"
              @click="currentPage--"
              class="px-3 py-1 border rounded disabled:opacity-50"
            >
              Prev
            </button>
            <button 
              :disabled="currentPage * pageSize >= users.length"
              @click="currentPage++"
              class="px-3 py-1 border rounded disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
  
      <!-- Модальное окно создания/редактирования пользователя -->
      <div 
        v-if="showCreateUserModal" 
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
      >
        <div class="bg-white p-6 rounded-lg w-96">
          <h2 class="text-xl font-bold mb-4">
            {{ editingUser ? 'Edit User' : 'Create User' }}
          </h2>
          <form @submit.prevent="saveUser">
            <div class="mb-4">
              <label class="block mb-2">Name</label>
              <input 
                v-model="userForm.name" 
                type="text" 
                required 
                class="w-full border px-3 py-2 rounded"
              >
            </div>
            <div class="mb-4">
              <label class="block mb-2">Email</label>
              <input 
                v-model="userForm.email" 
                type="email" 
                required 
                class="w-full border px-3 py-2 rounded"
              >
            </div>
            <div class="flex justify-end space-x-2">
              <button 
                type="button" 
                @click="showCreateUserModal = false"
                class="px-4 py-2 bg-gray-200 rounded"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              >
                Save
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        users: [],
        searchQuery: '',
        statusFilter: '',
        currentPage: 1,
        pageSize: 10,
        showCreateUserModal: false,
        editingUser: null,
        userForm: {
          name: '',
          email: ''
        }
      }
    },
    computed: {
      filteredUsers() {
        return this.users.filter(user => {
          const matchesSearch = user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                 user.email.toLowerCase().includes(this.searchQuery.toLowerCase())
          const matchesStatus = !this.statusFilter || user.status === this.statusFilter
          return matchesSearch && matchesStatus
        }).slice((this.currentPage - 1) * this.pageSize, this.currentPage * this.pageSize)
      }
    },
    methods: {
      async fetchUsers() {
        try {
          const response = await axios.get('/api/admin/users')
          this.users = response.data.users
        } catch (error) {
          console.error('Failed to fetch users', error)
        }
      },
      formatDate(date) {
        return new Date(date).toLocaleDateString()
      },
      editUser(user) {
        this.editingUser = user
        this.userForm = { ...user }
        this.showCreateUserModal = true
      },
      async changeUserStatus(user) {
        try {
          await axios.patch(`/api/admin/users/${user.id}/status`, {
            status: user.status === 'active' ? 'suspended' : 'active'
          })
          this.fetchUsers()
        } catch (error) {
          console.error('Failed to change user status', error)
        }
      },
      async saveUser() {
        try {
          if (this.editingUser) {
            // Обновление существующего пользователя
            await axios.put(`/api/admin/users/${this.editingUser.id}`, this.userForm)
          } else {
            // Создание нового пользователя
            await axios.post('/api/admin/users', this.userForm)
          }
          this.fetchUsers()
          this.showCreateUserModal = false
          this.resetForm()
        } catch (error) {
          console.error('Failed to save user', error)
        }
      },
      resetForm() {
        this.userForm = { name: '', email: '' }
        this.editingUser = null
      }
    },
    mounted() {
      this.fetchUsers()
    }
  }
  </script>