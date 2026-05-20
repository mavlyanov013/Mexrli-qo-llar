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
                    <RefreshCw class="w-3.5 h-3.5" />
                    <span>{{ t('todayDonations.updatedAt') }}: {{ updatedAt }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div
                    v-for="(item, index) in cards"
                    :key="index"
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm"
                >
                    <IconBadge
                        :icon="item.icon"
                        :tone="item.tone"
                        size="md"
                        class="mb-4"
                    />

                    <p class="text-3xl font-bold text-gray-900">
                        {{ item.value }}
                    </p>

                    <p class="text-sm text-gray-500 mt-2">
                        {{ item.label }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { RefreshCw, Wallet, Banknote, Users } from 'lucide-vue-next'
import api from '@/services/api'
import IconBadge from '../shared/IconBadge.vue'

const { t, locale } = useI18n()

const donations = ref([])
const updatedAtRaw = ref(null)

let intervalId = null

// 🔥 DATE CHECK
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

// 🔥 FETCH FROM DONATIONS (NOT PAYMENTS)
const fetchData = async () => {
    try {
        const res = await api.get('/donations') // ✅ ONLY THIS

        const list = res.data.data ?? []

        donations.value = list
        updatedAtRaw.value = new Date().toISOString()

    } catch (error) {
        console.error(error)
        donations.value = []
    }
}

// 🔥 TODAY FILTER (CASH IS INSIDE DONATIONS NOW)
const todayCash = computed(() => {
    const list = Array.isArray(donations.value) ? donations.value : []

    return list.filter(item => {
        const date = new Date(item.created_at)

        return (
            item.provider === 'cash' &&
            item.status === 'completed' &&
            isTodayDate(item.created_at)
        )
    })
})

// 🔥 TOTAL CASH
const cashAmount = computed(() => {
    return todayCash.value.reduce((sum, item) => {
        return sum + Number(item.amount || 0)
    }, 0)
})

// 🔥 UNIQUE DONORS
const donors = computed(() => {
    return new Set(
        todayCash.value
            .map(item => item.donor_phone || item.donor_name)
            .filter(Boolean)
    ).size
})

// 🔥 UPDATED TIME
const updatedAt = computed(() => {
    if (!updatedAtRaw.value) return '—'

    const localeMap = {
        en: 'en-US',
        uz: 'uz-UZ',
        ru: 'ru-RU',
    }

    return new Date(updatedAtRaw.value).toLocaleString(
        localeMap[locale.value] || 'en-US',
        {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    )
})

const formatNumber = (value) => {
    return Number(value || 0).toLocaleString()
}

// 🔥 CARDS
const cards = computed(() => [
    {
        label: t('todayDonations.cashAmount'),
        value: `${formatNumber(cashAmount.value)} UZS`,
        tone: 'yellow',
        icon: Banknote,
    },
    {
        label: t('todayDonations.uniqueDonors'),
        value: donors.value,
        tone: 'orange',
        icon: Users,
    },
    {
        label: t('todayDonations.totalDonations'),
        value: `${donations.value.length}`,
        tone: 'green',
        icon: Wallet,
    },
])

onMounted(async () => {
    await fetchData()
    intervalId = setInterval(fetchData, 60000)
})

onBeforeUnmount(() => {
    if (intervalId) clearInterval(intervalId)
})
</script>
