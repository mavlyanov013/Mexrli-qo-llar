<template>
    <div class="bg-white rounded-2xl p-6 border border-gray-100">
        <div class="flex items-center gap-3 mb-5">
            <IconBadge :icon="TrendingUp" tone="green" size="sm" />
            <h3 class="font-bold text-gray-900">{{ t('transparencyPage.liveDonationsFeed') }}</h3>
            <div class="ml-auto w-2 h-2 rounded-full bg-green-500 animate-pulse" />
        </div>

        <div class="space-y-3 max-h-96 overflow-y-auto">
            <div
                v-for="donation in donations"
                :key="donation.id"
                class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors"
            >
                <IconBadge :icon="Heart" tone="red" size="sm" class="shrink-0" />

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                        {{ donation.is_anonymous ? t('transparencyPage.anonymous') : donation.donor_name }}
                        {{ t('transparencyPage.donated') }}
                        <span class="text-[#4CAF50] font-bold">
                            {{ formatMoney(donation.amount) }}
                        </span>
                    </p>

                    <p class="text-xs text-gray-500">
                        → {{ t('transparencyPage.forCase') }} {{ getCaseName(donation.case_id) }}
                    </p>

                    <p class="text-xs text-gray-400 mt-1">
                        {{ timeAgo(donation.created_at) }}
                    </p>
                </div>
            </div>

            <div v-if="donations.length === 0" class="text-center py-8 text-gray-400">
                <IconBadge :icon="Heart" tone="red" size="md" class="mx-auto mb-2 opacity-40" />
                <p class="text-sm">{{ t('transparencyPage.noRecentDonations') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Heart, TrendingUp } from 'lucide-vue-next'
import IconBadge from '../shared/IconBadge.vue'
import { formatMoneyAmount } from '@/utils/formatAmount'

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

const formatMoney = (amount) => formatMoneyAmount(amount, props.t('common.currencyCode'))

const getCaseName = (caseId) => {
    if (!caseId) return props.t('transparencyPage.generalFund')
    const found = props.cases.find((c) => c.id === caseId)
    return found ? found.name : props.t('transparencyPage.generalFund')
}

const timeAgo = (dateString) => {
    if (!dateString) return props.t('transparencyPage.justNow')

    const now = new Date()
    const date = new Date(dateString)
    const diff = Math.floor((now - date) / 1000)

    if (diff < 60) return props.t('transparencyPage.justNow')
    if (diff < 3600) {
        return props.t('common.timeAgo.minutes', { n: Math.floor(diff / 60) })
    }
    if (diff < 86400) {
        return props.t('common.timeAgo.hours', { n: Math.floor(diff / 3600) })
    }
    return props.t('common.timeAgo.days', { n: Math.floor(diff / 86400) })
}
</script>
