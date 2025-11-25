<template>
    <div class="container mx-auto px-4 py-6">
      <h1 class="text-2xl font-bold mb-6">Редактирование Проекта</h1>
  
      <form 
        v-if="project" 
        @submit.prevent="updateProject" 
        class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-md"
      >
        <div class="mb-4">
          <label class="block mb-2 font-semibold">Название проекта</label>
          <input
            v-model="projectForm.name"
            type="text"
            required
            placeholder="Введите название проекта"
            class="w-full border px-3 py-2 rounded"
          >
        </div>
  
        <div class="mb-4">
          <label class="block mb-2 font-semibold">Описание</label>
          <textarea
            v-model="projectForm.description"
            rows="4"
            placeholder="Краткое описание проекта"
            class="w-full border px-3 py-2 rounded"
          ></textarea>
        </div>
  
        <div class="mb-4">
          <label class="block mb-2 font-semibold">Видимость проекта</label>
          <select 
            v-model="projectForm.visibility" 
            class="w-full border px-3 py-2 rounded"
          >
            <option value="private">Приватный (только я)</option>
            <option value="shared">Общий (с участниками)</option>
            <option value="public">Публичный (виден всем)</option>
          </select>
        </div>
  
        <div class="flex justify-end space-x-4">
          <router-link 
            :to="{ name: 'projects.index' }" 
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300"
          >
            Отмена
          </router-link>
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          >
            Обновить проект
          </button>
        </div>
      </form>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        project: null,
        projectForm: {
          name: '',
          description: '',
          visibility: 'private'
        }
      }
    },
    methods: {
      async fetchProject() {
        try {
          const response = await this.$axios.get(`/projects/${this.$route.params.id}`)
          this.project = response.data.project
          this.projectForm = { 
            name: this.project.name,
            description: this.project.description,
            visibility: this.project.visibility
          }
        } catch (error) {
          this.$toast.error('Не удалось загрузить проект')
          this.$router.push({ name: 'projects.index' })
        }
      },
      async updateProject() {
        try {
          await this.$axios.put(`/projects/${this.$route.params.id}`, this.projectForm)
          this.$toast.success('Проект успешно обновлен')
          this.$router.push({ name: 'projects.index' })
        } catch (error) {
          this.$toast.error('Не удалось обновить проект')
        }
      }
    },
    mounted() {
      this.fetchProject()
    }
  }
  </script>