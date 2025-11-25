import iziToast from 'izitoast'

export default {
    install: (app) => {
        const toast = {
            success(message, title = 'Успех') {
                iziToast.success({ 
                    title, 
                    message,
                    position: 'topRight'
                })
            },
            error(message, title = 'Ошибка') {
                iziToast.error({ 
                    title, 
                    message,
                    position: 'topRight'
                })
            },
            info(message, title = 'Информация') {
                iziToast.info({ 
                    title, 
                    message,
                    position: 'topRight'
                })
            },
            warning(message, title = 'Внимание') {
                iziToast.warning({ 
                    title, 
                    message,
                    position: 'topRight'
                })
            }
        }

        app.config.globalProperties.$toast = toast
    }
}