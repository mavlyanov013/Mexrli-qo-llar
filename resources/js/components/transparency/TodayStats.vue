<template>
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div
                v-for="(stat, index) in stats"
                :key="index"
                class="bg-white rounded-2xl p-6 border border-gray-100"
            >
                <IconBadge
                    :icon="stat.icon"
                    :tone="stat.tone"
                    size="md"
                    class="mb-4"
                />

                <p class="text-3xl font-bold text-gray-900">{{ stat.value }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ stat.label }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Wallet, HandCoins, Users } from 'lucide-vue-next'
import IconBadge from '../shared/IconBadge.vue'

const props = defineProps({
    donations: {
        type: Array,
        default: () => [],
    },
    t: {
        type: Function,
        required: true,
    },
})

const isTodayDate = (dateString) => {
    if (!dateString) return false
    const input = new Date(dateString)
    const today = new Date()

    return (
        input.getFullYear() === today.getFullYear() &&
        input.getMonth() === today.getMonth() &&
        input.getDate() === today.getDate()
    )
}

const todayStats = computed(() => {
    const todayDonations = props.donations.filter(
        (d) => d.created_at && isTodayDate(d.created_at)
    )

    const totalAmount = todayDonations.reduce((sum, d) => sum + Number(d.amount || 0), 0)
    const uniqueDonors = new Set(
        todayDonations.map((d) => d.donor_email).filter(Boolean)
    ).size

    return {
        totalAmount,
        donationsCount: todayDonations.length,
        activeDonors: uniqueDonors,
    }
})

const stats = computed(() => [
    {
        label: props.t('transparencyPage.onlineAmount'),
        value: `${todayStats.value.totalAmount.toLocaleString()} UZS`,
        icon: Wallet,
        tone: 'green',
    },
    {
        label: props.t('transparencyPage.totalToday'),
        value: `${todayStats.value.totalAmount.toLocaleString()} UZS`,
        icon: HandCoins,
        tone: 'yellow',
    },
    {
        label: props.t('transparencyPage.activeDonorsToday'),
        value: todayStats.value.activeDonors,
        icon: Users,
        tone: 'orange',
    },
])
</script>
