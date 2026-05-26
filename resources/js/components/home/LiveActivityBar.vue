<template>
    <div
        class="sticky top-[72px] z-30 w-full bg-red-50/95 backdrop-blur border-b border-red-100 py-2 cursor-pointer"
        @click="goToLive"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center gap-3 text-sm">
            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse shrink-0"></span>

            <span class="font-semibold text-red-600 shrink-0">
                {{ t('liveActivityBar.label') }}
            </span>

            <span class="text-gray-700 truncate">
                {{ latestText }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import donationService from '../../services/donationService'
import { formatMoneyAmount } from '@/utils/formatAmount'

const router = useRouter()
const { t } = useI18n()

const donations = ref([])
const currentIndex = ref(0)
const latestText = ref('')

let rotateInterval = null
let fetchInterval = null

const formatAmountDisplay = (amount) => formatMoneyAmount(amount, t('common.currencyCode'))

const donorLabel = (donation) => {
    if (donation.is_anonymous) {
        return t('common.anonymous')
    }

    return donation.donor_name || t('common.donor')
}

const fetchLiveDonations = async () => {
    try {
        const list = await donationService.getCompletedDonations()
        donations.value = Array.isArray(list) ? list : []

        if (donations.value.length > 0) {
            updateText()
        } else {
            latestText.value = t('liveActivityBar.empty')
        }
    } catch (error) {
        console.error('Live donations error:', error)
        latestText.value = t('liveActivityBar.error')
    }
}

const updateText = () => {
    if (!donations.value.length) return

    const d = donations.value[currentIndex.value]

    latestText.value = t('liveActivityBar.donated', {
        name: donorLabel(d),
        amount: formatAmountDisplay(d.amount),
    })

    currentIndex.value = (currentIndex.value + 1) % donations.value.length
}

const goToLive = () => {
    router.push('/live-donations')
}

onMounted(async () => {
    latestText.value = t('liveActivityBar.loading')
    await fetchLiveDonations()

    rotateInterval = setInterval(() => {
        updateText()
    }, 3000)

    fetchInterval = setInterval(() => {
        fetchLiveDonations()
    }, 15000)
})

onBeforeUnmount(() => {
    if (rotateInterval) clearInterval(rotateInterval)
    if (fetchInterval) clearInterval(fetchInterval)
})
</script>
