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
                        {{ Number(donation.amount || 0).toLocaleString() }} UZS
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

        <div
            v-if="totalPages > 1"
            class="px-6 py-4 border-t border-gray-100 flex items-center justify-between flex-wrap gap-3"
        >
            <p class="text-sm text-gray-500">
                {{ startItem }}-{{ endItem }} / {{ filteredDonations.length }}
            </p>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm border border-gray-300 bg-white text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="currentPage === 1"
                    @click="prevPage"
                >
                    Prev
                </button>

                <div class="flex items-center gap-2 flex-wrap">
                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        type="button"
                        class="min-w-10 h-10 rounded-xl text-sm border transition-all"
                        :class="page === currentPage
                            ? 'bg-[#2A7DE1] text-white border-[#2A7DE1]'
                            : 'bg-white text-gray-700 border-gray-300'"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </button>
                </div>

                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm border border-gray-300 bg-white text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="currentPage === totalPages"
                    @click="nextPage"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

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
const currentPage = ref(1)
const perPage = 10

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

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredDonations.value.length / perPage))
})

const paginatedDonations = computed(() => {
    const start = (currentPage.value - 1) * perPage
    const end = start + perPage

    return filteredDonations.value.slice(start, end)
})

const startItem = computed(() => {
    if (!filteredDonations.value.length) return 0
    return (currentPage.value - 1) * perPage + 1
})

const endItem = computed(() => {
    return Math.min(currentPage.value * perPage, filteredDonations.value.length)
})

const visiblePages = computed(() => {
    const pages = []
    const maxVisible = 5
    let start = Math.max(1, currentPage.value - 2)
    let end = Math.min(totalPages.value, start + maxVisible - 1)

    if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
})

const changeFilter = (value) => {
    filter.value = value
    currentPage.value = 1
}

const goToPage = (page) => {
    currentPage.value = page
}

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

watch(filteredDonations, () => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = 1
    }
})

const getCaseName = (caseId) => {
    if (!caseId) return props.t('transparencyPage.generalFund')
    const found = props.cases.find((c) => c.id === caseId)
    return found ? found.name : props.t('transparencyPage.generalFund')
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleString()
}
</script>
