<template>
    <div class="container mx-auto px-4 py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Мои Проекты</h1>
        <router-link 
          :to="{ name: 'projects.create' }" 
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Создать Проект
        </router-link>
      </div>
  
      <!-- Фильтры и поиск -->
      <div class="mb-4 flex space-x-4">
        <input 
          type="text" 
          v-model="searchQuery"
          placeholder="Поиск проектов..."
          class="border px-3 py-2 rounded w-full"
        >
        <select v-model="visibilityFilter" class="border px-3 py-2 rounded">
          <option value="">Все проекты</option>
          <option value="private">Приватные</option>
          <option value="shared">Общие</option>
          <option value="public">Публичные</option>
        </select>
      </div>
  
      <!-- Список проектов -->
      <div 
        v-if="projects.length > 0" 
        class="grid md:grid-cols-2 lg:grid-cols-3 gap-4"
      >
        <div 
          v-for="project in filteredProjects" 
          :key="project.id"
          class="bg-white shadow rounded-lg p-6 hover:shadow-md transition"
        >
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">{{ project.name }}</h2>
            <span 
              :class="{
                'bg-green-100 text-green-800': project.visibility === 'public',
                'bg-yellow-100 text-yellow-800': project.visibility === 'shared',
                'bg-gray-100 text-gray-800': project.visibility === 'private'
              }"
              class="px-2 py-1 rounded text-sm"
            >
              {{ getVisibilityLabel(project.visibility) }}
            </span>
          </div>
  
          <p class="text-gray-600 mb-4">
            {{ project.description || 'Без описания' }}
          </p>
  
          <div class="flex justify-between items-center">
            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-500">
                Ассистентов: {{ project.assistants_count || 0 }}
              </span>
            </div>
  
            <div class="flex space-x-2">
              <router-link 
                :to="{ name: 'projects.edit', params: { id: project.id } }"
                class="text-blue-500 hover:text-blue-700"
              >
                Изменить
              </router-link>
              <button 
                @click="deleteProject(project)"
                class="text-red-500 hover:text-red-700"
              >
                Удалить
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <div 
        v-else 
        class="bg-gray-100 text-center p-10 rounded-lg"
      >
        <p class="text-gray-600">У вас пока нет проектов</p>
        <router-link 
          :to="{ name: 'projects.create' }" 
          class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Создать первый проект
        </router-link>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        projects: [],
        searchQuery: '',
        visibilityFilter: ''
      }
    },
    computed: {
      filteredProjects() {
        return this.projects.filter(project => {
          const matchesSearch = project.name.toLowerCase().includes(this.searchQuery.toLowerCase())
          const matchesVisibility = !this.visibilityFilter || project.visibility === this.visibilityFilter
          return matchesSearch && matchesVisibility
        })
      }
    },
    methods: {
      getVisibilityLabel(visibility) {
        const labels = {
          'private': 'Приватный',
          'shared': 'Общий',
          'public': 'Публичный'
        }
        return labels[visibility]
      },
      async fetchProjects() {
        try {
          const response = await this.$axios.get('/projects')
          this.projects = response.data.projects
        } catch (error) {
          this.$toast.error('Не удалось загрузить проекты')
        }
      },
      async deleteProject(project) {
        if (confirm(`Вы уверены, что хотите удалить проект "${project.name}"?`)) {
          try {
            await this.$axios.delete(`/projects/${project.id}`)
            this.projects = this.projects.filter(p => p.id !== project.id)
            this.$toast.success('Проект успешно удален')
          } catch (error) {
            this.$toast.error('Не удалось удалить проект')
          }
        }
      }
    },
    mounted() {
      this.fetchProjects()
    }
  }
  </script>