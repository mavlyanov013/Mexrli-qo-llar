<template>
    <div class="pt-24 pb-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 bg-red-50 text-red-500 px-4 py-2 rounded-full text-sm font-medium mb-5">
                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse" />
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
                    <p class="text-sm text-gray-500 mb-1">Bugungi summa</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ formatNumber(todayTotal) }} UZS
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Bugungi xayriyalar</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ todayDonations.length }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Noyob donorlar</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ uniqueDonors }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-3">
                    <span class="text-[#2A7DE1] text-xl">❤</span>
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
                        v-for="item in donations"
                        :key="item.id"
                        class="p-5 flex items-center justify-between gap-4"
                    >
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ item.is_anonymous ? 'Anonim' : item.donor_name || 'Donor' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ formatDateTime(item.created_at || item.created_date) }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="font-bold text-[#2A7DE1]">
                                {{ formatNumber(item.amount) }} UZS
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ item.status }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import donationService from '../services/donationService'

const donations = ref([])
const loading = ref(false)
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
