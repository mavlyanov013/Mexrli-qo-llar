<template>
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
            <h3 class="font-bold text-gray-900 text-lg">
                {{ t('transparencyPage.paymentHistory') }}
            </h3>

            <div class="flex gap-2 flex-wrap">
                <button
                    v-for="item in filters"
                    :key="item.id"
                    @click="changeFilter(item.id)"
                    class="rounded-xl px-4 py-2 text-sm border transition-all"
                    :class="filter === item.id
                        ? 'bg-[#2A7DE1] text-white border-[#2A7DE1]'
                        : 'bg-white text-gray-700 border-gray-300'"
                >
                    {{ item.label }}
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left p-4 font-semibold text-gray-600">{{ t('transparencyPage.donor') }}</th>
                    <th class="text-left p-4 font-semibold text-gray-600">{{ t('transparencyPage.amount') }}</th>
                    <th class="text-left p-4 font-semibold text-gray-600">{{ t('transparencyPage.supportedCase') }}</th>
                    <th class="text-left p-4 font-semibold text-gray-600">{{ t('transparencyPage.date') }}</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                <tr v-if="paginatedDonations.length === 0">
                    <td colspan="4" class="text-center py-8 text-gray-500">
                        {{ t('transparencyPage.noDonationsFound') }}
                    </td>
                </tr>

                <tr
                    v-for="donation in paginatedDonations"
                    :key="donation.id"
                    class="hover:bg-gray-50"
                >
                    <td class="p-4 font-medium">
                        {{ donation.is_anonymous ? t('transparencyPage.anonymous') : donation.donor_name }}
                    </td>
                    <td class="p-4 font-bold text-[#4CAF50]">
                        {{ formatMoney(donation.amount) }}
                    </td>
                    <td class="p-4">
                        {{ getCaseName(donation.case_id) }}
                    </td>
                    <td class="p-4 text-gray-500">
                        {{ formatDate(donation.created_at) }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <Pagination
            wrapper-class="px-6 py-4 border-t border-gray-100"
            :current-page="currentPage"
            :last-page="totalPages"
            :start-item="startItem"
            :end-item="endItem"
            :total-items="filteredDonations.length"
            :visible-pages="visiblePages"
            @change="goToPage"
            @prev="prevPage"
            @next="nextPage"
        />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { formatMoneyAmount } from '@/utils/formatAmount'
import Pagination from '@/components/shared/Pagination.vue'
import { useClientPagination } from '@/composables/useClientPagination'

const props = defineProps({
    donations: {
        type: Array,
        default: () => [],
    },
    cases: {
        type: Array,
        default: () => [],
    },
    t: {
        type: Function,
        required: true,
    },
})

const filter = ref('all')

const filters = computed(() => [
    { id: 'all', label: props.t('transparencyPage.filters.all') },
    { id: 'today', label: props.t('transparencyPage.filters.today') },
    { id: 'week', label: props.t('transparencyPage.filters.week') },
    { id: 'month', label: props.t('transparencyPage.filters.month') },
])

const isTodayDate = (dateString) => {
    const input = new Date(dateString)
    const today = new Date()

    return (
        input.getFullYear() === today.getFullYear() &&
        input.getMonth() === today.getMonth() &&
        input.getDate() === today.getDate()
    )
}

const isThisWeekDate = (dateString) => {
    const input = new Date(dateString)
    const now = new Date()
    const firstDay = new Date(now)
    firstDay.setDate(now.getDate() - now.getDay())
    firstDay.setHours(0, 0, 0, 0)

    const lastDay = new Date(firstDay)
    lastDay.setDate(firstDay.getDate() + 6)
    lastDay.setHours(23, 59, 59, 999)

    return input >= firstDay && input <= lastDay
}

const isThisMonthDate = (dateString) => {
    const input = new Date(dateString)
    const now = new Date()

    return (
        input.getFullYear() === now.getFullYear() &&
        input.getMonth() === now.getMonth()
    )
}

const filteredDonations = computed(() => {
    let result = props.donations || []

    if (filter.value === 'today') {
        result = result.filter((d) => d.created_at && isTodayDate(d.created_at))
    } else if (filter.value === 'week') {
        result = result.filter((d) => d.created_at && isThisWeekDate(d.created_at))
    } else if (filter.value === 'month') {
        result = result.filter((d) => d.created_at && isThisMonthDate(d.created_at))
    }

    return result
})

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
} = useClientPagination(filteredDonations, 10)

const changeFilter = (value) => {
    filter.value = value
    currentPage.value = 1
}

const getCaseName = (caseId) => {
    if (!caseId) return props.t('transparencyPage.generalFund')
    const found = props.cases.find((c) => c.id === caseId)
    return found ? found.name : props.t('transparencyPage.generalFund')
}

const formatMoney = (amount) => formatMoneyAmount(amount, props.t('common.currencyCode'))

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleString()
}
</script>
