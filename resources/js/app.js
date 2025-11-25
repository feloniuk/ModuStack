import '../css/app.css'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import NotificationPlugin from './plugins/notifications'
import axios from 'axios'
import router from './router'

createInertiaApp({
    title: (title) => `AI Assistant - ${title}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(NotificationPlugin)
            .use(router)

        // Глобальная настройка axios
        app.config.globalProperties.$axios = axios
        axios.defaults.baseURL = '/api'

        // Перехватчик для обновления токена и данных пользователя
        axios.interceptors.response.use(response => {
            // Если пришел новый токен в ответе
            if (response.data.token) {
                localStorage.setItem('auth_token', response.data.token)
            }
            // Если пришли данные пользователя
            if (response.data.user) {
                localStorage.setItem('user', JSON.stringify(response.data.user))
            }
            return response
        }, error => {
            // Обработка ошибок
            return Promise.reject(error)
        })

        app.mount(el)
    },
})