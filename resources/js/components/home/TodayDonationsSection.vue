<template>
    <section class="py-14 bg-gradient-to-br from-[#2A7DE1]/5 to-[#4CAF50]/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm font-semibold text-[#2A7DE1] uppercase tracking-widest mb-1">
                        {{ t('todayDonations.live') }}
                    </p>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                        {{ t('todayDonations.title') }}
                    </h2>
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-400">
                    <span>⟳</span>
                    <span>{{ t('todayDonations.updatedAt') }}: {{ updatedAt }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div
                    v-for="(item, index) in cards"
                    :key="index"
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm"
                >
                    <div
                        class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                        :class="item.color"
                    >
                        <span v-if="item.type !== 'users'" class="text-lg font-bold">UZS</span>
                        <span v-else class="text-lg font-bold">👥</span>
                    </div>

                    <p class="text-3xl font-bold text-gray-900">{{ item.value }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ item.label }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import donationService from '@/services/donationService'

const { t, locale } = useI18n()

const editing = ref(false)
const cashInput = ref('')
const cashAmount = ref(0)
const donations = ref([])
const updatedAtRaw = ref(null)

let intervalId = null

const todayKey = computed(() => {
    const d = new Date()
    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    return `cash_donations_${yyyy}-${mm}-${dd}`
})

const loadCash = () => {
    const saved = localStorage.getItem(todayKey.value)
    cashAmount.value = saved ? Number(saved) : 0
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

const fetchData = async () => {
    try {
        const data = await donationService.getCompletedDonations()

        donations.value = Array.isArray(data?.data)
            ? data.data
            : Array.isArray(data)
                ? data
                : []

        updatedAtRaw.value = new Date().toISOString()
    } catch (error) {
        console.error('Today donations fetch error:', error)
        donations.value = []
    }
}

const stats = computed(() => {
    const donationList = Array.isArray(donations.value) ? donations.value : []

    const todayDonations = donationList.filter((item) =>
        isTodayDate(item.created_at || item.created_date)
    )

    const total = todayDonations.reduce((sum, item) => {
        return sum + Number(item.amount || 0)
    }, 0)

    const donors = new Set(
        todayDonations
            .map((item) => item.donor_email)
            .filter(Boolean)
    ).size

    return {
        total,
        count: todayDonations.length,
        donors,
    }
})

const updatedAt = computed(() => {
    if (!updatedAtRaw.value) return '—'

    const localeMap = {
        en: 'en-US',
        uz: 'uz-UZ',
        ru: 'ru-RU',
    }

    return new Date(updatedAtRaw.value).toLocaleString(localeMap[locale.value] || 'en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
})

const formatNumber = (value) => {
    return Number(value || 0).toLocaleString()
}

const cards = computed(() => [
    {
        type: 'money',
        label: t('todayDonations.onlineAmount'),
        value: `${formatNumber(stats.value.total)} UZS`,
        color: 'bg-green-50 text-[#4CAF50]',
    },
    {
        type: 'money',
        label: t('todayDonations.cashAmount'),
        value: `${formatNumber(cashAmount.value)} UZS`,
        color: 'bg-yellow-50 text-yellow-600',
    },
    {
        type: 'users',
        label: t('todayDonations.uniqueDonors'),
        value: stats.value.donors,
        color: 'bg-orange-50 text-[#FF9800]',
    },
])

onMounted(async () => {
    loadCash()
    await fetchData()
    intervalId = setInterval(fetchData, 60000)
})

onBeforeUnmount(() => {
    if (intervalId) clearInterval(intervalId)
})
</script>
