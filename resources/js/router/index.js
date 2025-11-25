import { createRouter, createWebHistory } from 'vue-router'

// Динамический импорт компонентов
const Dashboard = () => import('../Pages/Dashboard.vue')
const AdminDashboard = () => import('../Pages/Admin/Dashboard.vue')
const Users = () => import('../Pages/Admin/Users.vue')
const Plans = () => import('../Pages/Admin/Plans.vue')
const Providers = () => import('../Pages/Admin/Providers.vue')
const Assistants = () => import('../Pages/Assistants/Index.vue')
const AssistantCreate = () => import('../Pages/Assistants/Create.vue')
const Projects = () => import('../Pages/Projects/Index.vue')
const ProjectCreate = () => import('../Pages/Projects/Create.vue')
const Login = () => import('../Pages/Auth/Login.vue')
const Register = () => import('../Pages/Auth/Register.vue')

const routes = [
    { 
        path: '/login', 
        component: Login, 
        name: 'login',
        meta: { guest: true }
    },
    { 
        path: '/register', 
        component: Register, 
        name: 'register',
        meta: { guest: true }
    },
    {
        path: '/dashboard',
        component: Dashboard,
        name: 'dashboard',
        meta: { requiresAuth: true }
    },
    {
        path: '/assistants',
        component: Assistants,
        name: 'assistants.index',
        meta: { requiresAuth: true }
    },
    {
        path: '/assistants/create',
        component: AssistantCreate,
        name: 'assistants.create',
        meta: { requiresAuth: true }
    },
    {
        path: '/projects',
        component: Projects,
        name: 'projects.index',
        meta: { requiresAuth: true }
    },
    {
        path: '/projects/create',
        component: ProjectCreate,
        name: 'projects.create',
        meta: { requiresAuth: true }
    },
    // Административные маршруты
    {
        path: '/admin/dashboard',
        component: AdminDashboard,
        name: 'admin.dashboard',
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/users',
        component: Users,
        name: 'admin.users',
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/plans',
        component: Plans,
        name: 'admin.plans',
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/providers',
        component: Providers,
        name: 'admin.providers',
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/:pathMatch(.*)*', 
        redirect: '/dashboard'
    }
]

const router = createRouter({
    history: createWebHistory('/'),
    routes
})

// Навигационные guard'ы
router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('auth_token')
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    
    // Для гостевых маршрутов
    if (to.meta.guest) {
        if (isAuthenticated) {
            // Редирект на dashboard для авторизованных
            next({ name: 'dashboard' })
        } else {
            next()
        }
        return
    }

    // Требуется авторизация
    if (to.meta.requiresAuth) {
        if (!isAuthenticated) {
            next({ name: 'login' })
            return
        }

        // Проверка прав администратора
        if (to.meta.requiresAdmin && !user.is_admin) {
            next({ name: 'dashboard' })
            return
        }
    }

    next()
})

export default router