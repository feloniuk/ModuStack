<template>
    <div class="container mx-auto px-4 py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Управление Тарифами</h1>
        <button 
          @click="openPlanModal(null)"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Создать Тариф
        </button>
      </div>
  
      <div class="grid md:grid-cols-3 gap-4">
        <div 
          v-for="plan in plans" 
          :key="plan.id"
          class="bg-white shadow rounded-lg p-6"
        >
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">{{ plan.name }}</h2>
            <span 
              :class="{
                'bg-green-100 text-green-800': !plan.is_free,
                'bg-blue-100 text-blue-800': plan.is_free
              }"
              class="px-2 py-1 rounded text-sm"
            >
              {{ plan.is_free ? 'Бесплатный' : 'Платный' }}
            </span>
          </div>
  
          <div class="mb-4">
            <p class="text-2xl font-bold">{{ plan.price }} ₴/мес</p>
          </div>
  
          <ul class="mb-4 space-y-2">
            <li 
              v-for="(feature, index) in parsePlanFeatures(plan.features)" 
              :key="index"
              class="flex items-center"
            >
              <i class="fas fa-check text-green-500 mr-2"></i>
              {{ feature }}
            </li>
          </ul>
  
          <div class="flex justify-between">
            <button 
              @click="openPlanModal(plan)"
              class="text-blue-500 hover:text-blue-700"
            >
              Изменить
            </button>
            <button 
              @click="deletePlan(plan)"
              class="text-red-500 hover:text-red-700"
            >
              Удалить
            </button>
          </div>
        </div>
      </div>
  
      <!-- Modal для создания/редактирования тарифа -->
      <div 
        v-if="planModal.show" 
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
      >
        <div class="bg-white p-6 rounded-lg w-96">
          <h2 class="text-xl font-bold mb-4">
            {{ planModal.plan ? 'Редактировать' : 'Создать' }} Тариф
          </h2>
  
          <form @submit.prevent="savePlan">
            <div class="mb-4">
              <label>Название</label>
              <input 
                v-model="planModal.form.name" 
                type="text" 
                required 
                class="w-full border px-3 py-2 rounded"
              >
            </div>
  
            <div class="mb-4">
              <label>Цена (₴)</label>
              <input 
                v-model.number="planModal.form.price" 
                type="number" 
                required 
                class="w-full border px-3 py-2 rounded"
              >
            </div>
  
            <div class="mb-4">
              <label>Возможности (через запятую)</label>
              <textarea 
                v-model="planModal.form.features" 
                class="w-full border px-3 py-2 rounded"
                rows="4"
              ></textarea>
            </div>
  
            <div class="flex justify-end space-x-2">
              <button 
                type="button" 
                @click="planModal.show = false" 
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
              >
                Отмена
              </button>
              <button 
                type="submit" 
                class="bg-blue-500 text-white px-4 py-2 rounded"
              >
                Сохранить
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
        plans: [],
        planModal: {
          show: false,
          plan: null,
          form: {
            name: '',
            price: 0,
            features: ''
          }
        }
      }
    },
    methods: {
      parsePlanFeatures(features) {
        try {
          const parsedFeatures = JSON.parse(features)
          return [
            `${parsedFeatures.requests_per_day} запросов в день`,
            `Модели: ${parsedFeatures.ai_models.join(', ')}`,
            parsedFeatures.max_assistants ? `До ${parsedFeatures.max_assistants} ассистентов` : 'Неограниченное кол-во ассистентов'
          ]
        } catch {
          return []
        }
      },
      openPlanModal(plan) {
        this.planModal.show = true
        if (plan) {
          this.planModal.plan = plan
          this.planModal.form = {
            name: plan.name,
            price: plan.price,
            features: JSON.stringify(plan.features, null, 2)
          }
        } else {
          this.planModal.plan = null
          this.planModal.form = {
            name: '',
            price: 0,
            features: JSON.stringify({
              requests_per_day: 50,
              ai_models: ['huggingface_free'],
              max_assistants: 1
            }, null, 2)
          }
        }
      },
      async fetchPlans() {
        try {
          const response = await this.$axios.get('/admin/plans')
          this.plans = response.data.plans
        } catch {
          this.$toast.error('Не удалось загрузить тарифы')
        }
      },
      async savePlan() {
        try {
          const features = JSON.parse(this.planModal.form.features)
          const payload = {
            ...this.planModal.form,
            features,
            is_free: features.requests_per_day === 50
          }
  
          if (this.planModal.plan) {
            await this.$axios.put(`/admin/plans/${this.planModal.plan.id}`, payload)
          } else {
            await this.$axios.post('/admin/plans', payload)
          }
          
          this.$toast.success('Тариф сохранен')
          this.planModal.show = false
          this.fetchPlans()
        } catch (error) {
          this.$toast.error('Не удалось сохранить тариф')
        }
      },
      async deletePlan(plan) {
        if (confirm(`Удалить тариф "${plan.name}"?`)) {
          try {
            await this.$axios.delete(`/admin/plans/${plan.id}`)
            this.$toast.success('Тариф удален')
            this.fetchPlans()
          } catch {
            this.$toast.error('Не удалось удалить тариф')
          }
        }
      }
    },
    mounted() {
      this.fetchPlans()
    }
  }
  </script>