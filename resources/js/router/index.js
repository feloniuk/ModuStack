import { createRouter, createWebHistory } from 'vue-router'

// Динамический импорт компонентов
const Dashboard = () => import('../Pages/Dashboard.vue')
const Welcome = () => import('../Pages/Welcome.vue')
const ProjectEdit = () => import('../Pages/Projects/Edit.vue')

const routes = [
    { 
        path: '/', 
        component: Welcome, 
        name: 'welcome' 
    },
    { 
        path: '/dashboard', 
        component: Dashboard, 
        name: 'dashboard',
        meta: { requiresAuth: true }
    },
    {
        path: '/projects/edit/:id',
        component: ProjectEdit,
        name: 'projects.edit',
        meta: { requiresAuth: true }
    }
]

const router = createRouter({
    history: createWebHistory('/'),
    routes
})

router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('auth_token')
    
    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'welcome' })
        return
    }

    next()
})

export default router