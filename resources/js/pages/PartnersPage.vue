<template>
    <div class="pt-24 pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto mb-16 max-w-3xl text-center">
                <h1 class="mb-4 text-4xl font-bold text-gray-900 md:text-5xl">
                    {{ t('partnersPage.title') }}
                </h1>
                <p class="text-lg text-gray-500">
                    {{ t('partnersPage.subtitle') }}
                </p>
            </div>

            <ListState :loading="loading" :error="error" :empty="partners.length === 0">
                <template #loading>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="i in 6" :key="i" class="animate-pulse rounded-2xl border border-gray-100 bg-white p-6">
                            <div class="mb-4 h-16 w-16 rounded-xl bg-gray-200" />
                            <div class="mb-2 h-5 w-2/3 rounded bg-gray-200" />
                            <div class="mb-4 h-4 w-1/3 rounded bg-gray-100" />
                            <div class="h-4 w-full rounded bg-gray-100" />
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div class="py-20 text-center">
                        <div class="mb-4 text-5xl text-gray-300">🏢</div>
                        <p class="text-gray-500">{{ t('partnersPage.empty') }}</p>
                    </div>
                </template>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="partner in partners" :key="partner.id" class="rounded-2xl border border-gray-100 bg-white p-6">
                        <div class="flex items-start gap-4">
                            <img v-if="partner.logo_url" :src="partner.logo_url" :alt="partner.name" class="h-16 w-16 rounded-xl object-contain" />
                            <div v-else class="flex h-16 w-16 items-center justify-center rounded-xl bg-gray-100 text-xl font-bold text-gray-400">
                                {{ partner.name?.[0] }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900">{{ partner.name }}</h3>
                                <span class="mt-1 inline-flex rounded-full border-0 px-3 py-1 text-sm font-medium" :class="typeClass(partner.type)">
                                    {{ formatType(partner.type) }}
                                </span>
                            </div>
                        </div>

                        <p v-if="partner.description" class="mt-4 text-sm text-gray-500">
                            {{ partner.description }}
                        </p>

                        <ExternalLink v-if="partner.website" :href="partner.website" classes="mt-3 inline-flex items-center gap-1 text-sm text-[#2A7DE1] hover:underline">
                            {{ t('partnersPage.visitWebsite') }} ↗
                        </ExternalLink>
                    </div>
                </div>
            </ListState>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import ListState from '@/components/shared/ListState.vue'
import ExternalLink from '@/components/shared/ExternalLink.vue'
import { PARTNER_TYPES } from '@/constants/partners'
import { usePartners } from '@/composables/usePartners'

const { t } = useI18n()
const { partners, loading, error } = usePartners()

const formatType = (value) => (value || 'corporate').replace(/_/g, ' ')

const typeClass = (type) => {
    if (!PARTNER_TYPES.includes(type)) return 'bg-blue-50 text-[#2A7DE1]'
    if (type === 'ngo') return 'bg-green-50 text-[#4CAF50]'
    if (type === 'government') return 'bg-purple-50 text-purple-600'
    if (type === 'medical') return 'bg-red-50 text-red-600'
    if (type === 'media') return 'bg-orange-50 text-[#FF9800]'
    return 'bg-blue-50 text-[#2A7DE1]'
}
</script>
