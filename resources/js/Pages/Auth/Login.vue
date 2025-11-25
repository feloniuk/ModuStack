<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div>
          <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Вход в систему
          </h2>
        </div>
        <form 
          @submit.prevent="login" 
          class="mt-8 space-y-6"
        >
          <div class="rounded-md shadow-sm -space-y-px">
            <div>
              <label for="email" class="sr-only">Email</label>
              <input 
                v-model="email" 
                id="email" 
                type="email" 
                required 
                class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                placeholder="Email"
              >
            </div>
            <div>
              <label for="password" class="sr-only">Пароль</label>
              <input 
                v-model="password" 
                id="password" 
                type="password" 
                required 
                class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                placeholder="Пароль"
              >
            </div>
          </div>
  
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input 
                v-model="rememberMe" 
                id="remember-me" 
                type="checkbox" 
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              >
              <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                Запомнить меня
              </label>
            </div>
  
            <div class="text-sm">
              <a 
                href="/forgot-password" 
                class="font-medium text-indigo-600 hover:text-indigo-500"
              >
                Забыли пароль?
              </a>
            </div>
          </div>
  
          <div>
            <button 
              type="submit" 
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Войти
            </button>
          </div>
        </form>
  
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">
                Или продолжить через
              </span>
            </div>
          </div>
  
          <div class="mt-6 grid grid-cols-2 gap-3">
            <button 
              @click="socialLogin('google')"
              class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              Google
            </button>
            <button 
              @click="socialLogin('linkedin')"
              class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              LinkedIn
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    methods: {
      async login() {
        try {
          const response = await this.$axios.post('/login', {
            email: this.email,
            password: this.password,
            remember: this.rememberMe
          })
  
          // Сохраняем токен
          localStorage.setItem('auth_token', response.data.token)
  
          // Используем Inertia для навигации
          this.$inertia.visit(
            response.data.user.is_admin ? '/admin/dashboard' : '/dashboard'
          )
        } catch (error) {
          // Обработка ошибок происходит в axios interceptor
        }
      }
    }
  }
  </script>