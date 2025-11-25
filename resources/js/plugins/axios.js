import axios from 'axios'

export default function(app) {
  const toast = app.config.globalProperties.$toast

  // Конфигурация базового URL
  axios.defaults.baseURL = '/api'

  // Interceptor для запросов
  axios.interceptors.request.use(
    config => {
      // Показываем глобальный лоадер
      document.dispatchEvent(new CustomEvent('loading:start'))
      
      // Добавляем токен аутентификации
      const token = localStorage.getItem('auth_token')
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`
      }
      
      return config
    },
    error => {
      document.dispatchEvent(new CustomEvent('loading:end'))
      return Promise.reject(error)
    }
  )

  // Interceptor для ответов
  axios.interceptors.response.use(
    response => {
      document.dispatchEvent(new CustomEvent('loading:end'))
      
      // Опциональные успешные уведомления
      if (response.config.method !== 'get' && response.data.message) {
        toast.success(response.data.message)
      }
      
      return response
    },
    error => {
      document.dispatchEvent(new CustomEvent('loading:end'))
      
      // Обработка ошибок
      if (error.response) {
        const status = error.response.status
        const data = error.response.data

        switch (status) {
          case 401:  // Unauthorized
            toast.error('Сессия истекла. Пожалуйста, войдите заново.')
            // Редирект на страницу входа
            window.location.href = '/login'
            break
          case 403:  // Forbidden
            toast.error('У вас недостаточно прав для этого действия')
            break
          case 422:  // Validation errors
            const errors = data.errors || {}
            Object.values(errors).forEach(errorList => {
              errorList.forEach(errorMsg => toast.error(errorMsg))
            })
            break
          case 500:  // Server error
            toast.error('Внутренняя ошибка сервера. Попробуйте позже.')
            break
          default:
            toast.error(data.message || 'Произошла неизвестная ошибка')
        }
      } else if (error.request) {
        // Запрос был сделан, но нет ответа (например, проблемы с сетью)
        toast.error('Проблемы с подключением. Проверьте интернет.')
      } else {
        toast.error('Ошибка при выполнении запроса')
      }
      
      return Promise.reject(error)
    }
  )

  // Добавляем axios в глобальные свойства приложения
  app.config.globalProperties.$axios = axios
}