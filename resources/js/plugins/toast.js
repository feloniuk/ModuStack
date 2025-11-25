import { createApp } from 'vue'
import Toast from 'vue-toast-notification'
import 'vue-toast-notification/dist/theme-sugar.css'

export default {
  install(app) {
    const toast = {
      success(message) {
        app.config.globalProperties.$toast.success(message)
      },
      error(message) {
        app.config.globalProperties.$toast.error(message)
      },
      info(message) {
        app.config.globalProperties.$toast.info(message)
      },
      warning(message) {
        app.config.globalProperties.$toast.warning(message)
      }
    }

    app.config.globalProperties.$toast = toast
    app.use(Toast, {
      position: 'top-right',
      duration: 3000,
      dismissible: true
    })
  }
}