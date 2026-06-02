<template>
    <div class="pt-24 pb-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 bg-red-50 text-red-500 px-4 py-2 rounded-full text-sm font-medium mb-5">
                    <IconBadge :icon="Radio" tone="red" size="xs" class="shrink-0" />
                    {{ t('liveDonationsPage.badge') }}
                </div>

                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-3">
                    {{ t('liveDonationsPage.title') }}
                </h1>
                <p class="text-gray-500 text-lg">
                    {{ t('liveDonationsPage.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <IconBadge :icon="Wallet" tone="green" size="md" />
                    </div>
                    <p class="text-sm text-gray-500 mb-1">{{ t('liveDonationsPage.todayAmount') }}</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ formatMoney(todayTotal) }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <IconBadge :icon="HandHeart" tone="blue" size="md" />
                    </div>
                    <p class="text-sm text-gray-500 mb-1">{{ t('liveDonationsPage.todayCount') }}</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ todayDonations.length }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <IconBadge :icon="Users" tone="orange" size="md" />
                    </div>
                    <p class="text-sm text-gray-500 mb-1">{{ t('liveDonationsPage.uniqueDonors') }}</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ uniqueDonors }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-3">
                    <IconBadge :icon="Heart" tone="red" size="sm" />

                    <h2 class="font-bold text-gray-900 text-lg">
                        {{ t('liveDonationsPage.recentTitle') }}
                    </h2>
                </div>

                <div v-if="loading" class="p-6 text-gray-500">
                    {{ t('common.loading') }}
                </div>

                <div v-else-if="donations.length === 0" class="p-6 text-gray-500">
                    {{ t('liveDonationsPage.empty') }}
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="item in paginatedDonations"
                        :key="item.id"
                        class="p-5 flex items-center justify-between gap-4"
                    >
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900">
                                {{ item.is_anonymous ? t('common.anonymous') : (item.donor_name || t('common.donor')) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ formatDateTime(item.created_at || item.created_date) }}
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="font-bold text-[#2A7DE1]">
                                {{ formatMoney(item.amount) }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ item.status }}
                            </p>
                        </div>
                    </div>
                </div>

                <Pagination
                    wrapper-class="px-6 py-4 border-t border-gray-100"
                    :current-page="currentPage"
                    :last-page="totalPages"
                    :start-item="startItem"
                    :end-item="endItem"
                    :total-items="donations.length"
                    :visible-pages="visiblePages"
                    @change="goToPage"
                    @prev="prevPage"
                    @next="nextPage"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Heart, HandHeart, Radio, Users, Wallet } from 'lucide-vue-next'
import IconBadge from '@/components/shared/IconBadge.vue'
import Pagination from '@/components/shared/Pagination.vue'
import donationService from '../services/donationService'
import { formatMoneyAmount } from '@/utils/formatAmount'
import { useClientPagination } from '@/composables/useClientPagination'

const { t, locale } = useI18n()

const donations = ref([])
const loading = ref(false)
let intervalId = null

const {
    currentPage,
    totalPages,
    paginatedItems: paginatedDonations,
    startItem,
    endItem,
    visiblePages,
    goToPage,
    prevPage,
    nextPage,
} = useClientPagination(donations, 10)

const fetchDonations = async () => {
    loading.value = true

    try {
        const data = await donationService.getCompletedDonations()
        donations.value = Array.isArray(data) ? data : []
    } catch (error) {
        console.error('Live donations error:', error)
        donations.value = []
    } finally {
        loading.value = false
    }
}

const isTodayDate = (value) => {
    if (!value) return false

    const date = new Date(value)
    const today = new Date()

    return (
        date.getFullYear() === today.getFullYear() &&
        date.getMonth() === today.getMonth() &&
        date.getDate() === today.getDate()
    )
}

const todayDonations = computed(() => {
    return donations.value.filter((item) =>
        isTodayDate(item.created_at || item.created_date)
    )
})

const todayTotal = computed(() => {
    return todayDonations.value.reduce((sum, item) => {
        return sum + Number(item.amount || 0)
    }, 0)
})

const uniqueDonors = computed(() => {
    return new Set(
        todayDonations.value.map((item) => item.donor_email).filter(Boolean)
    ).size
})

const formatMoney = (value) => formatMoneyAmount(value, t('common.currencyCode'))

const formatDateTime = (value) => {
    if (!value) return ''
    const localeCode = locale.value === 'ru' ? 'ru-RU' : 'uz-UZ'
    return new Date(value).toLocaleString(localeCode)
}

onMounted(async () => {
    await fetchDonations()
    intervalId = setInterval(fetchDonations, 15000)
})

onBeforeUnmount(() => {
    if (intervalId) clearInterval(intervalId)
})
</script>
