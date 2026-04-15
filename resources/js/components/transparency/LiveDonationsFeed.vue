<template>
    <div class="bg-white rounded-2xl p-6 border border-gray-100">
        <div class="flex items-center gap-2 mb-5">
            <span class="text-[#4CAF50] text-lg">↗</span>
            <h3 class="font-bold text-gray-900">{{ t('transparencyPage.liveDonationsFeed') }}</h3>
            <div class="ml-auto w-2 h-2 rounded-full bg-green-500 animate-pulse" />
        </div>

        <div class="space-y-3 max-h-96 overflow-y-auto">
            <div
                v-for="donation in donations"
                :key="donation.id"
                class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors"
            >
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                    <span class="text-[#2A7DE1]">❤</span>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                        {{ donation.is_anonymous ? t('transparencyPage.anonymous') : donation.donor_name }}
                        {{ t('transparencyPage.donated') }}
                        <span class="text-[#4CAF50] font-bold">
                            {{ Number(donation.amount || 0).toLocaleString() }} UZS
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
                <div class="text-2xl mb-2">♡</div>
                <p class="text-sm">{{ t('transparencyPage.noRecentDonations') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
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
    if (diff < 3600) return `${Math.floor(diff / 60)} min ago`
    if (diff < 86400) return `${Math.floor(diff / 3600)} h ago`
    return `${Math.floor(diff / 86400)} d ago`
}
</script>
