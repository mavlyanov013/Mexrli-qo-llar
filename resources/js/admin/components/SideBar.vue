<script setup>
import { useI18n } from 'vue-i18n'
import {
    LayoutDashboard,
    Handshake,
    UserCog,
    HandCoins,
    HeartPulse,
    HandHeart,
    Users,
    Newspaper,
    CircleHelp,
    CreditCard,
    FileText,
    ClipboardList,
    BarChart3,
    Settings,
    Mail,
} from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute } from 'vue-router'

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

const navItems = computed(() => [
    { to: '/admin/dashboard', label: t('admin.dashboard'), icon: LayoutDashboard },
    { to: '/admin/partners', label: t('admin.partners'), icon: Handshake },
    { to: '/admin/users', label: t('admin.users'), icon: UserCog },
    { to: '/admin/payments', label: t('admin.payments'), icon: CreditCard },
    { to: '/admin/donations', label: t('admin.donations'), icon: HandCoins },
    { to: '/admin/cases', label: t('admin.cases'), icon: HeartPulse },
    { to: '/admin/help-requests', label: "Yordam so‘rovlari", icon: ClipboardList },
    { to: '/admin/about-sections', label: "About bo‘limlari", icon: FileText },
    { to: '/admin/news', label: "Yangiliklar", icon: Newspaper },
    { to: '/admin/faq', label: "FAQ", icon: CircleHelp },
    { to: '/admin/blog', label: t('admin.blog'), icon: Newspaper },
    { to: '/admin/volunteers', label: t('admin.volunteers'), icon: Users },
    { to: '/admin/messages', label: t('admin.messages'), icon: Mail },
    { to: '/admin/pages', label: t('admin.pages'), icon: FileText },
    { to: '/admin/reports', label: t('admin.reports'), icon: BarChart3 },
    { to: '/admin/settings', label: t('admin.settings'), icon: Settings },
])
</script>

<template>
    <div
        v-if="isOpen"
        class="lg:hidden fixed inset-0 z-40 bg-black/30"
        @click="$emit('close')"
    />

    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col p-4 transition-transform duration-200',
            isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
    >
        <div class="mb-8 flex items-start justify-between">
            <div class="flex items-center gap-3">
                <img
                    src="/public/images/logo.png"
                    alt="Mehrli Insonlar"
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

        <nav class="space-y-2">
            <router-link
                v-for="item in navItems"
                :key="item.to"
                :to="item.to"
                @click="$emit('close')"
                :class="[
                    'w-full text-left px-4 py-3 rounded-xl text-sm font-medium transition-colors flex items-center gap-3',
                    route.path.startsWith(item.to)
                        ? 'bg-[#2A7DE1] text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-50'
                ]"
            >
                <component :is="item.icon" class="w-4.5 h-4.5" />
                <span>{{ item.label }}</span>
            </router-link>
        </nav>
    </aside>
</template>
