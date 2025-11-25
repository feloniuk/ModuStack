<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
      <div class="text-center">
        <h1 class="text-4xl font-bold mb-4">Добро пожаловать в AI Assistant</h1>
        <div class="space-x-4">
          <router-link 
            v-if="!$page.props.auth.user" 
            to="/login" 
            class="bg-blue-500 text-white px-4 py-2 rounded"
          >
            Войти
          </router-link>
          <router-link 
            v-if="!$page.props.auth.user" 
            to="/register" 
            class="bg-green-500 text-white px-4 py-2 rounded"
          >
            Регистрация
          </router-link>
          <router-link 
            v-if="$page.props.auth.user" 
            to="/dashboard" 
            class="bg-blue-500 text-white px-4 py-2 rounded"
          >
            Панель управления
          </router-link>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Welcome'
  }
  </script>