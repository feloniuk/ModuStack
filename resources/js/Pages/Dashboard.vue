<template>
    <div class="min-h-screen bg-gray-100 p-6">
      <h1 class="text-3xl font-bold mb-6">Панель управления</h1>
      <div class="bg-white shadow rounded-lg p-6">
        <p>Добро пожаловать, {{ $page.props.auth.user.name }}!</p>
        
        <div class="mt-4 grid grid-cols-3 gap-4">
          <div class="bg-blue-100 p-4 rounded">
            <h3 class="font-semibold">Ассистенты</h3>
            <p>Создайте своего AI помощника</p>
          </div>
          <div class="bg-green-100 p-4 rounded">
            <h3 class="font-semibold">Проекты</h3>
            <p>Управляйте проектами</p>
          </div>
          <div class="bg-purple-100 p-4 rounded">
            <h3 class="font-semibold">Интеграции</h3>
            <p>Подключите сервисы</p>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Dashboard'
  }
  </script>