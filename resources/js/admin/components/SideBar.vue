<script setup>
import { useI18n } from 'vue-i18n'
import {
    LayoutDashboard,
    HeartPulse,
    HandHeart,
    LifeBuoy,
    Users,
    Mail,
    Newspaper,
    CreditCard,
} from 'lucide-vue-next'

const activeTab = defineModel()
defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
})
const emit = defineEmits(['close'])

const { t } = useI18n()

const tabs = [
    { id: 'overview', labelKey: 'admin.overview', icon: LayoutDashboard },
    { id: 'cases', labelKey: 'admin.cases', icon: HeartPulse },
    { id: 'donations', labelKey: 'admin.donations', icon: HandHeart },
    { id: 'help-requests', labelKey: 'admin.helpRequests', icon: LifeBuoy },
    { id: 'volunteers', labelKey: 'admin.volunteers', icon: Users },
    { id: 'messages', labelKey: 'admin.messages', icon: Mail },
    { id: 'blog', labelKey: 'admin.blog', icon: Newspaper },
    { id: 'payments', labelKey: 'admin.payments', icon: CreditCard },
]

const selectTab = (tabId) => {
    activeTab.value = tabId
    emit('close')
}
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
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="selectTab(tab.id)"
                :class="[
                    'w-full text-left px-4 py-3 rounded-xl text-sm font-medium transition-colors flex items-center gap-3',
                    activeTab === tab.id
                        ? 'bg-[#2A7DE1] text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-50'
                ]"
            >
                <component :is="tab.icon" class="w-4.5 h-4.5" />
                <span>{{ t(tab.labelKey) }}</span>
            </button>
        </nav>
    </aside>
</template>
