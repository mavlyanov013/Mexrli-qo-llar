<script setup>
import { useI18n } from 'vue-i18n'

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
    { id: 'overview', labelKey: 'admin.overview', icon: '📊' },
    { id: 'cases', labelKey: 'admin.cases', icon: '🩺' },
    { id: 'donations', labelKey: 'admin.donations', icon: '💝' },
    { id: 'help-requests', labelKey: 'admin.helpRequests', icon: '🆘' },
    { id: 'volunteers', labelKey: 'admin.volunteers', icon: '🤝' },
    { id: 'messages', labelKey: 'admin.messages', icon: '✉️' },
    { id: 'blog', labelKey: 'admin.blog', icon: '📰' },
    { id: 'payments', labelKey: 'admin.payments', icon: '💳' },
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
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ t('admin.panel') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ t('admin.management') }}</p>
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
                <span class="text-base">{{ tab.icon }}</span>
                <span>{{ t(tab.labelKey) }}</span>
            </button>
        </nav>
    </aside>
</template>
