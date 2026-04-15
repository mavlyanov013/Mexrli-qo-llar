<template>
    <div class="min-h-screen bg-[#F8FAFC] flex">
        <Sidebar v-model="activeTab" :is-open="sidebarOpen" @close="sidebarOpen = false" />

        <main class="flex-1 lg:ml-64 min-w-0">
            <div class="sticky top-0 z-30 bg-[#F8FAFC]/90 backdrop-blur border-b border-gray-100">
                <div class="flex items-center justify-between gap-4 p-4 lg:p-6">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl border border-gray-200 bg-white text-gray-700"
                            @click="sidebarOpen = true"
                        >
                            ☰
                        </button>

                        <div>
                            <h1 class="text-xl lg:text-2xl font-bold text-gray-900">
                                {{ pageTitle }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                {{ t('admin.management') }}
                            </p>
                        </div>
                    </div>

                    <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 shadow-sm">
                        <button
                            v-for="lang in languages"
                            :key="lang"
                            @click="setLocale(lang)"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition',
                                locale === lang
                                    ? 'bg-[#2A7DE1] text-white'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            {{ lang.toUpperCase() }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-4 pb-6 lg:px-8 lg:pb-8 pt-4">
                <router-view v-slot="{ Component }">
                    <component :is="Component" :active-tab="activeTab" />
                </router-view>
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import Sidebar from '../components/SideBar.vue'

const activeTab = ref('overview')
const sidebarOpen = ref(false)
const { locale, t } = useI18n()

const languages = ['en', 'uz', 'ru']

const pageTitle = computed(() => {
    const map = {
        overview: t('admin.dashboard'),
        cases: t('admin.cases'),
        donations: t('admin.donations'),
        'help-requests': t('admin.helpRequests'),
        volunteers: t('admin.volunteers'),
        messages: t('admin.messages'),
        blog: t('admin.blog'),
        payments: t('admin.payments'),
    }

    return map[activeTab.value] || t('admin.panel')
})

const setLocale = (lang) => {
    locale.value = lang
    localStorage.setItem('lang', lang)
}
</script>
