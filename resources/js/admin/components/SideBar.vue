<script setup>
import { useI18n } from 'vue-i18n'
import {
    LayoutDashboard,
    Handshake,
    UserCog,
    HandCoins,
    HeartPulse,
    Users,
    Newspaper,
    CircleHelp,
    CreditCard,
    FileText,
    ClipboardList,
    Mail,
    Activity,
    MapPin,
} from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { canAccessAdminTab } from '@/admin/utils/permissions'
import { usePermissions } from '@/composables/usePermissions'
import { siteLogo } from '@/constants/branding'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
})

const { t } = useI18n()
const route = useRoute()
const emit = defineEmits(['close'])
const { isSuperAdmin } = usePermissions()

const navItems = computed(() => [
    { to: '/admin/dashboard', label: t('admin.dashboard'), icon: LayoutDashboard, tab: 'overview' },
    { to: '/admin/partners', label: t('admin.partners'), icon: Handshake, tab: 'partners' },
    { to: '/admin/users', label: t('admin.users'), icon: UserCog, tab: 'users' },
    { to: '/admin/payments', label: t('admin.payments'), icon: CreditCard, tab: 'payments' },
    { to: '/admin/donations', label: t('admin.donations'), icon: HandCoins, tab: 'donations' },
    { to: '/admin/cases', label: t('admin.cases'), icon: HeartPulse, tab: 'cases' },
    { to: '/admin/help-requests', label: "Yordam so‘rovlari", icon: ClipboardList, tab: 'help-requests' },
    { to: '/admin/about-sections', label: 'Biz haqimizda', icon: FileText, tab: 'about-sections' },
    { to: '/admin/contact-info', label: "Aloqa ma'lumotlari", icon: MapPin, tab: 'contact-info' },
    { to: '/admin/news', label: "Yangiliklar", icon: Newspaper, tab: 'news' },
    { to: '/admin/faq', label: "FAQ-Ko‘p so‘raladigan savollar", icon: CircleHelp, tab: 'faq' },
    { to: '/admin/treatment-processes', label: 'Davolanish jarayoni', icon: Activity, tab: 'treatment-processes' },
    { to: '/admin/volunteers', label: t('admin.volunteers'), icon: Users, tab: 'volunteers' },
    { to: '/admin/messages', label: t('admin.messages'), icon: Mail, tab: 'messages' },
])

const visibleNavItems = computed(() => navItems.value.filter((item) => {
    if ((item.tab === 'payments' || item.tab === 'donations' || item.tab === 'users') && !isSuperAdmin.value) {
        return false
    }

    return canAccessAdminTab(props.user, item.tab)
}))
</script>

<template>
    <div
        v-if="isOpen"
        class="lg:hidden fixed inset-0 z-40 bg-black/30"
        @click="$emit('close')"
    />

    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 w-64 h-screen bg-white border-r border-gray-200 flex flex-col overflow-hidden p-4 transition-transform duration-200',
            isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
    >
        <div class="shrink-0 mb-4 flex items-start justify-between">
            <div class="flex items-center gap-3">
                <img
                    :src="siteLogo"
                    :alt="t('common.brandName')"
                    class="w-11 h-11 object-contain rounded-xl"
                />

                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ t('admin.panel') }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ t('admin.management') }}</p>
                </div>
            </div>

            <button
                type="button"
                class="lg:hidden w-9 h-9 rounded-lg border border-gray-200 text-gray-600"
                @click="$emit('close')"
            >
                ✕
            </button>
        </div>

        <nav class="sidebar-nav flex-1 min-h-0 overflow-y-auto overscroll-contain space-y-2 pr-1 -mr-1">
            <router-link
                v-for="item in visibleNavItems"
                :key="item.to"
                :to="item.to"
                @click="$emit('close')"
                :class="[
                    'w-full text-left px-4 py-3 rounded-xl text-sm font-medium transition-colors flex items-center gap-3 shrink-0',
                    route.path.startsWith(item.to)
                        ? 'bg-[#2A7DE1] text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-50'
                ]"
            >
                <component :is="item.icon" class="w-4.5 h-4.5 shrink-0" />
                <span class="leading-snug">{{ item.label }}</span>
            </router-link>
        </nav>
    </aside>
</template>

<style scoped>
.sidebar-nav {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 9999px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}
</style>
