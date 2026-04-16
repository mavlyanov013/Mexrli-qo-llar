<template>
    <div class="pt-24 pb-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 bg-red-50 text-red-500 px-4 py-2 rounded-full text-sm font-medium mb-5">
                    <Radio class="w-4 h-4" />
                    Jonli
                </div>

                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-3">
                    Jonli xayriyalar
                </h1>
                <p class="text-gray-500 text-lg">
                    Xayriyalar real vaqt rejimida
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-green-50 text-[#4CAF50] flex items-center justify-center">
                            <Wallet class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mb-1">Bugungi summa</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ formatNumber(todayTotal) }} UZS
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#2A7DE1] flex items-center justify-center">
                            <HandHeart class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mb-1">Bugungi xayriyalar</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ todayDonations.length }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#FF9800] flex items-center justify-center">
                            <Users class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mb-1">Noyob donorlar</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ uniqueDonors }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center">
                        <Heart class="w-5 h-5" />
                    </div>

                    <h2 class="font-bold text-gray-900 text-lg">
                        So‘nggi xayriyalar
                    </h2>
                </div>

                <div v-if="loading" class="p-6 text-gray-500">
                    Yuklanmoqda...
                </div>

                <div v-else-if="donations.length === 0" class="p-6 text-gray-500">
                    Hozircha xayriyalar yo‘q.
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="item in paginatedDonations"
                        :key="item.id"
                        class="p-5 flex items-center justify-between gap-4"
                    >
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900">
                                {{ item.is_anonymous ? 'Anonim' : item.donor_name || 'Donor' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ formatDateTime(item.created_at || item.created_date) }}
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="font-bold text-[#2A7DE1]">
                                {{ formatNumber(item.amount) }} UZS
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ item.status }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="totalPages > 1"
                    class="px-6 py-4 border-t border-gray-100 flex items-center justify-between flex-wrap gap-3"
                >
                    <p class="text-sm text-gray-500">
                        {{ startItem }}-{{ endItem }} / {{ donations.length }}
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
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Heart, HandHeart, Radio, Users, Wallet } from 'lucide-vue-next'
import donationService from '../services/donationService'

const donations = ref([])
const loading = ref(false)
const currentPage = ref(1)
const perPage = 10
let intervalId = null

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

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(donations.value.length / perPage))
})

const paginatedDonations = computed(() => {
    const start = (currentPage.value - 1) * perPage
    const end = start + perPage
    return donations.value.slice(start, end)
})

const startItem = computed(() => {
    if (!donations.value.length) return 0
    return (currentPage.value - 1) * perPage + 1
})

const endItem = computed(() => {
    return Math.min(currentPage.value * perPage, donations.value.length)
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

const formatNumber = (value) => Number(value || 0).toLocaleString()

const formatDateTime = (value) => {
    if (!value) return ''
    return new Date(value).toLocaleString()
}

onMounted(async () => {
    await fetchDonations()
    intervalId = setInterval(fetchDonations, 15000)
})

onBeforeUnmount(() => {
    if (intervalId) clearInterval(intervalId)
})
</script>
