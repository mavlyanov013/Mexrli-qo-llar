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
                            <img v-if="partner.logo_url" :src="partner.logo_url" :alt="content(partner, 'name')" class="h-16 w-16 rounded-xl object-contain" />
                            <div v-else class="flex h-16 w-16 items-center justify-center rounded-xl bg-gray-100 text-xl font-bold text-gray-400">
                                {{ content(partner, 'name')?.[0] }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900">{{ content(partner, 'name') }}</h3>
                                <span class="mt-1 inline-flex rounded-full border-0 px-3 py-1 text-sm font-medium" :class="typeClass(partner.type)">
                                    {{ formatType(partner.type) }}
                                </span>
                            </div>
                        </div>

                        <p v-if="content(partner, 'description')" class="mt-4 text-sm text-gray-500">
                            {{ content(partner, 'description') }}
                        </p>

                        <ExternalLink v-if="partner.website" :href="partner.website" classes="mt-3 inline-flex items-center gap-1 text-sm text-[#2A7DE1] hover:underline">
                            {{ t('partnersPage.visitWebsite') }} ↗
                        </ExternalLink>
                    </div>
                </div>

                <Pagination
                    v-if="meta && meta.last_page > 1"
                    class="mt-10"
                    :current-page="meta.current_page || 1"
                    :last-page="meta.last_page || 1"
                    :start-item="startItem"
                    :end-item="endItem"
                    :total-items="meta.total || 0"
                    :visible-pages="visiblePages"
                    @change="fetchPage"
                    @prev="fetchPage((meta.current_page || 1) - 1)"
                    @next="fetchPage((meta.current_page || 1) + 1)"
                />
            </ListState>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLocalizedDisplay } from '@/composables/useLocalizedDisplay'
import ListState from '@/components/shared/ListState.vue'
import ExternalLink from '@/components/shared/ExternalLink.vue'
import Pagination from '@/components/shared/Pagination.vue'
import { PARTNER_TYPE_LABELS } from '@/constants/partners'
import { usePartners } from '@/composables/usePartners'

const { t } = useI18n()
const { content } = useLocalizedDisplay()
const { partners, meta, loading, error, fetchPartners } = usePartners({ autoFetch: false })

const perPage = 9

const visiblePages = computed(() => {
    const current = meta.value?.current_page || 1
    const last = meta.value?.last_page || 1
    const pages = []
    const maxVisible = 5
    let start = Math.max(1, current - 2)
    let end = Math.min(last, start + maxVisible - 1)

    if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
    }

    for (let i = start; i <= end; i += 1) {
        pages.push(i)
    }

    return pages
})

const startItem = computed(() => {
    if (!meta.value?.total) return 0
    return ((meta.value.current_page || 1) - 1) * (meta.value.per_page || perPage) + 1
})

const endItem = computed(() => {
    if (!meta.value?.total) return 0
    return Math.min(
        (meta.value.current_page || 1) * (meta.value.per_page || perPage),
        meta.value.total,
    )
})

const fetchPage = (page = 1) => {
    fetchPartners({ page, per_page: perPage })
}

const formatType = (value) => {
    const key = value || 'corporate'
    return t(`partnersPage.types.${key}`, PARTNER_TYPE_LABELS[key] || key)
}

const typeClass = (type) => {
    const map = {
        ngo: 'bg-green-50 text-[#4CAF50]',
        government: 'bg-purple-50 text-purple-600',
        medical: 'bg-red-50 text-red-600',
        media: 'bg-orange-50 text-[#FF9800]',
        corporate: 'bg-blue-50 text-[#2A7DE1]',
        foundation: 'bg-blue-50 text-[#2A7DE1]',
    }

    return map[type] || 'bg-blue-50 text-[#2A7DE1]'
}

onMounted(() => fetchPage(1))
</script>
