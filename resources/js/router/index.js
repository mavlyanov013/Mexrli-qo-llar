import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../pages/HomePage.vue'
import LoginPage from '../pages/LoginPage.vue'
import RegisterPage from '../pages/RegisterPage.vue'
import CasesPage from '../pages/CasesPage.vue'
import CaseDetailPage from '../pages/CaseDetailPage.vue'
import HelpRequestPage from '../pages/HelpRequestPage.vue'
import VolunteerPage from '../pages/VolunteerPage.vue'
import NewsPage from '../pages/NewsPage.vue'
import NewsDetailPage from '../pages/NewsDetailPage.vue'
import DonatePage from '../pages/DonatePage.vue'
import TransparencyPage from '../pages/TransparencyPage.vue'

const routes = [
    {
        path: '/',
        name: 'home',
        component: HomePage,
    },
    {
        path: '/login',
        name: 'login',
        component: LoginPage,
    },
    // {
    //     path: '/register',
    //     name: 'register',
    //     component: RegisterPage,
    // },
    {
        path: '/cases',
        name: 'cases',
        component: CasesPage,
    },
    {
        path: '/cases/:id',
        name: 'case-detail',
        component: CaseDetailPage,
    },
    {
        path: '/help-request',
        name: 'help-request',
        component: HelpRequestPage,
    },
    {
        path: '/volunteer',
        name: 'volunteer',
        component: VolunteerPage,
    },
    {
        path: '/news',
        name: 'news',
        component: NewsPage,
    },
    {
        path: '/news/:id',
        name: 'news-detail',
        component: NewsDetailPage,
    },
    {
        path: '/donate',
        name: 'donate',
        component: DonatePage,
    },
    {
        path: '/transparency',
        name: 'transparency',
        component: TransparencyPage,
    },
    {
        path: '/faq',
        name: 'faq',
        component: () => import('../pages/FAQPage.vue'),
    },
    {
        path: '/live-donations',
        name: 'live-donations',
        component: () => import('../pages/LiveDonationsPage.vue'),
    },
    {
        path: '/about',
        name: 'about',
        component: () => import('../pages/AboutPage.vue'),
    },
    {
        path: '/contact',
        name: 'contact',
        component: () => import('../pages/ContactPage.vue'),
    },
    {
        path: '/partners',
        name: 'partners',
        component: () => import('../pages/PartnersPage.vue'),
    },
    {
        path: '/admin',
        component: () => import('@/admin/layouts/AdminLayout.vue'),
        children: [
            {
                path: '',
                name: 'AdminDashboard',
                component: () => import('@/admin/pages/AdminDashboard.vue'),
                meta: { requiresAuth: true, admin: true }
            }
        ]
    },
    {
        path: '/donate/paynet-status',
        name: 'paynet-status',
        component: () => import('../pages/PaynetStatusPage.vue'),
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')
    const user = JSON.parse(localStorage.getItem('user') || 'null')

    if (to.meta.requiresAuth && !token) {
        return next('/login')
    }

    const isAdmin = user?.is_admin === true

    if (to.meta.admin && !isAdmin) {
        return next('/')
    }

    next()
})

export default router
