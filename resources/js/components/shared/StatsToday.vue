<template>
    <div :class="variant === 'section' ? 'py-14 bg-gradient-to-br from-[#2A7DE1]/5 to-[#4CAF50]/5' : ''">
        <div :class="variant === 'section' ? 'max-w-7xl mx-auto px-4 sm:px-6' : ''">
            <div
                v-if="variant === 'section'"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8"
            >
                <div>
                    <p class="text-sm font-semibold text-[#2A7DE1] uppercase tracking-widest mb-1">
                        {{ t('todayDonations.live') }}
                    </p>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                        {{ t('todayDonations.title') }}
                    </h2>
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-400">
                    <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
                    <span>{{ t('todayDonations.updatedAt') }}: {{ updatedAtLabel }}</span>
                </div>
            </div>

            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div
                    v-for="index in 3"
                    :key="index"
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm animate-pulse h-36"
                />
            </div>

            <div v-else-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ error }}
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-5">
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
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Banknote, CreditCard, RefreshCw, Users } from 'lucide-vue-next'
import statsService from '@/services/statsService'
import IconBadge from './IconBadge.vue'
import { formatAmount, formatMoneyAmount } from '@/utils/formatAmount'

const props = defineProps({
    variant: {
        type: String,
        default: 'section',
    },
    refreshIntervalMs: {
        type: Number,
        default: 60000,
    },
})

const { t, locale } = useI18n()

const stats = ref(null)
const loading = ref(true)
const error = ref('')
let intervalId = null

const formatNumber = (value) => formatAmount(value)

const updatedAtLabel = computed(() => {
    const raw = stats.value?.updated_at

    if (!raw) {
        return '—'
    }

    const localeMap = {
        uz: 'uz-UZ',
        uz_cyrl: 'uz-UZ',
        ru: 'ru-RU',
    }

    return new Date(raw).toLocaleString(localeMap[locale.value] || 'uz-UZ', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
})

const cards = computed(() => [
    {
        label: t('todayDonations.cashAmount'),
        value: formatMoneyAmount(stats.value?.cash_total, 'UZS'),
        tone: 'yellow',
        icon: Banknote,
    },
    {
        label: t('todayDonations.onlineAmount'),
        value: formatMoneyAmount(stats.value?.online_total, 'UZS'),
        tone: 'green',
        icon: CreditCard,
    },
    {
        label: t('todayDonations.uniqueDonors'),
        value: formatNumber(stats.value?.donors_count),
        tone: 'orange',
        icon: Users,
    },
])

const loadStats = async () => {
    loading.value = true
    error.value = ''

    try {
        stats.value = await statsService.fetchToday()
    } catch (err) {
        console.error('Today stats load error:', err)
        error.value = t('todayDonations.loadError')
        stats.value = null
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    await loadStats()

    if (props.refreshIntervalMs > 0) {
        intervalId = setInterval(loadStats, props.refreshIntervalMs)
    }
})

onBeforeUnmount(() => {
    if (intervalId) {
        clearInterval(intervalId)
    }
})
</script>
