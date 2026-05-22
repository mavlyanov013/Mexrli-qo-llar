import { createRouter, createWebHistory } from 'vue-router'
import i18n from '../i18n'
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
import { canAccessAdmin, canAccessAdminRoute, normalizeRole } from '@/admin/utils/permissions'

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
        path: '/news/:slug',
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
        redirect: '/admin/dashboard',
        children: [
            {
                path: 'dashboard',
                name: 'admin-dashboard',
                component: () => import('@/admin/pages/AdminDashboardPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'partners',
                name: 'admin-partners',
                component: () => import('@/admin/pages/AdminPartnersPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'partners/create',
                name: 'admin-partners-create',
                component: () => import('@/admin/pages/AdminPartnersPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'partners/:id/edit',
                name: 'admin-partners-edit',
                component: () => import('@/admin/pages/AdminPartnersPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'partners/:id',
                name: 'admin-partners-view',
                component: () => import('@/admin/pages/AdminPartnersPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'users',
                name: 'admin-users',
                component: () => import('@/admin/pages/AdminUsersListPage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'users/create',
                name: 'admin-users-create',
                component: () => import('@/admin/pages/AdminUsersCreatePage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'users/:id/edit',
                name: 'admin-users-edit',
                component: () => import('@/admin/pages/AdminUsersEditPage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'users/:id',
                name: 'admin-users-view',
                component: () => import('@/admin/pages/AdminUsersViewPage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'payments',
                name: 'admin-payments',
                component: () => import('@/admin/pages/AdminPaymentsPage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'donations',
                name: 'admin-donations',
                component: () => import('@/admin/pages/AdminDonationsPage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'donations/create',
                name: 'admin-donations-create',
                component: () => import('@/admin/pages/AdminDonationsPage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'donations/:id/edit',
                name: 'admin-donations-edit',
                component: () => import('@/admin/pages/AdminDonationsPage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'donations/:id',
                name: 'admin-donations-view',
                component: () => import('@/admin/pages/AdminDonationsPage.vue'),
                meta: { requiresAuth: true, admin: true, superAdminOnly: true }
            },
            {
                path: 'cases',
                name: 'admin-cases',
                component: () => import('@/admin/pages/AdminCasesPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'cases/create',
                name: 'admin-cases-create',
                component: () => import('@/admin/pages/AdminCasesPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'cases/:id/edit',
                name: 'admin-cases-edit',
                component: () => import('@/admin/pages/AdminCasesPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'cases/:id',
                name: 'admin-cases-view',
                component: () => import('@/admin/pages/AdminCasesPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'about-sections',
                name: 'admin-about-sections',
                component: () => import('@/admin/pages/AboutPageEditor.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'contact-info',
                name: 'admin-contact-info',
                component: () => import('@/admin/pages/AdminContactInfoPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'news',
                name: 'admin-news',
                component: () => import('@/admin/pages/AdminNewsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'news/create',
                name: 'admin-news-create',
                component: () => import('@/admin/pages/AdminNewsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'news/:id',
                name: 'admin-news-view',
                component: () => import('@/admin/pages/AdminNewsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'news/:id/edit',
                name: 'admin-news-edit',
                component: () => import('@/admin/pages/AdminNewsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'faq',
                name: 'admin-faq',
                component: () => import('@/admin/pages/AdminFaqPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'faq/create',
                name: 'admin-faq-create',
                component: () => import('@/admin/pages/AdminFaqPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'faq/:id/edit',
                name: 'admin-faq-edit',
                component: () => import('@/admin/pages/AdminFaqPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'treatment-processes',
                name: 'admin-treatment-processes',
                component: () => import('@/admin/pages/AdminTreatmentProcessPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'treatment-processes/:caseId',
                name: 'admin-treatment-processes-case',
                component: () => import('@/admin/pages/AdminTreatmentProcessPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'treatment-processes/:caseId/create',
                name: 'admin-treatment-processes-create',
                component: () => import('@/admin/pages/AdminTreatmentProcessPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'treatment-processes/:caseId/edit/:processId',
                name: 'admin-treatment-processes-edit',
                component: () => import('@/admin/pages/AdminTreatmentProcessPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'help-requests',
                name: 'admin-help-requests',
                component: () => import('@/admin/pages/AdminHelpRequestsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'help-requests/:id',
                name: 'admin-help-requests-view',
                component: () => import('@/admin/pages/AdminHelpRequestsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'blog',
                name: 'admin-blog',
                component: () => import('@/admin/pages/AdminBlogPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'blog/create',
                name: 'admin-blog-create',
                component: () => import('@/admin/pages/AdminPlaceholderPage.vue'),
                props: { title: 'Create blog post', description: 'Blog create page.' },
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'blog/:id/edit',
                name: 'admin-blog-edit',
                component: () => import('@/admin/pages/AdminPlaceholderPage.vue'),
                props: { title: 'Edit blog post', description: 'Blog edit page.' },
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'volunteers',
                name: 'admin-volunteers',
                component: () => import('@/admin/pages/AdminVolunteersPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'volunteers/:id',
                name: 'admin-volunteers-view',
                component: () => import('@/admin/pages/AdminVolunteersPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'volunteers/:id/edit',
                name: 'admin-volunteers-edit',
                component: () => import('@/admin/pages/AdminVolunteersPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'messages',
                name: 'admin-messages',
                component: () => import('@/admin/pages/AdminMessagesPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'messages/:id',
                name: 'admin-messages-view',
                component: () => import('@/admin/pages/AdminMessagesPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'pages',
                name: 'admin-pages',
                component: () => import('@/admin/pages/AdminPagesPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'pages/create',
                name: 'admin-pages-create',
                component: () => import('@/admin/pages/AdminPlaceholderPage.vue'),
                props: { title: 'Create page', description: 'CMS page create page.' },
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'pages/:id/edit',
                name: 'admin-pages-edit',
                component: () => import('@/admin/pages/AdminPlaceholderPage.vue'),
                props: { title: 'Edit page', description: 'CMS page edit page.' },
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'reports',
                name: 'admin-reports',
                component: () => import('@/admin/pages/AdminReportsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'reports/create',
                name: 'admin-reports-create',
                component: () => import('@/admin/pages/AdminReportsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'reports/:id/edit',
                name: 'admin-reports-edit',
                component: () => import('@/admin/pages/AdminReportsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'reports/:id',
                name: 'admin-reports-view',
                component: () => import('@/admin/pages/AdminReportsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
            {
                path: 'settings',
                name: 'admin-settings',
                component: () => import('@/admin/pages/AdminSettingsPage.vue'),
                meta: { requiresAuth: true, admin: true }
            },
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

    const isAdmin = canAccessAdmin(user)

    if (to.meta.admin && !isAdmin) {
        return next('/')
    }

    if (to.meta.admin && !canAccessAdminRoute(user, to.path)) {
        return next('/admin/dashboard')
    }

    if (to.meta.superAdminOnly && normalizeRole(user) !== 'super_admin') {
        return next({ name: 'admin-dashboard' })
    }

    if (/^\/admin\/(payments|donations|users)(\/|$)/.test(to.path) && normalizeRole(user) !== 'super_admin') {
        return next({ name: 'admin-dashboard' })
    }

    if (to.meta.admin || to.path.startsWith('/admin')) {
        i18n.global.locale.value = 'uz'
        localStorage.setItem('lang', 'uz')
    }

    next()
})

export default router
