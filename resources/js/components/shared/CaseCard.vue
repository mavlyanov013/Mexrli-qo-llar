<template>
    <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 group flex flex-col">
        <div class="relative overflow-hidden" style="aspect-ratio: 4/3">
            <img
                :src="caseData.photo_url || fallbackImage"
                :alt="caseData.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />

            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" />

            <span
                v-if="caseData.is_urgent"
                :class="[
                    'absolute top-3 left-3 inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border',
                    urgencyStyle
                ]"
            >
                {{ urgencyLabel }}
            </span>

            <div class="absolute bottom-3 left-3 flex items-center gap-1.5 bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full">
                <span class="text-xs font-semibold text-gray-700">{{ t('caseCard.verified') }}</span>
            </div>

            <div
                v-if="caseData.age"
                class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full"
            >
                <span class="text-xs font-semibold text-gray-700">
                    {{ t('caseCard.age', { age: caseData.age }) }}
                </span>
            </div>
        </div>

        <div class="p-5 flex flex-col flex-1">
            <div class="flex items-start justify-between mb-2 gap-3">
                <div>
                    <h3 class="font-bold text-lg text-gray-900">{{ caseData.name }}</h3>

                    <p
                        v-if="caseData.location"
                        class="text-xs text-gray-400 flex items-center gap-1 mt-0.5"
                    >
                        <MapPin class="w-3.5 h-3.5 text-red-400" />
                        {{ caseData.location }}
                    </p>
                </div>

                <ProgressRing :percentage="percentage" :size="52" :stroke-width="5" />
            </div>

            <p class="text-sm text-gray-600 line-clamp-2 mb-4 flex-1">
                {{ caseData.short_description }}
            </p>

            <div class="mb-4">
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="text-[#4CAF50] font-semibold">
                        {{ formatMoney(caseData.raised_amount || 0) }} {{ t('caseCard.raised') }}
                    </span>
                    <span class="text-gray-400">
                        {{ t('caseCard.of') }} {{ formatMoney(caseData.goal_amount || 0) }}
                    </span>
                </div>

                <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-1000"
                        :style="progressStyle"
                    />
                </div>

                <p
                    v-if="remaining > 0"
                    class="text-xs text-[#FF9800] font-semibold mt-1.5"
                >
                    {{ formatMoney(remaining) }} {{ t('caseCard.stillNeeded') }}
                </p>
            </div>

            <div class="flex gap-2 mt-auto">
                <RouterLink
                    :to="`/cases/${caseData.id}`"
                    class="flex-1"
                >
                    <button
                        class="w-full rounded-xl text-sm border border-gray-300 px-4 py-2 hover:bg-gray-50"
                    >
                        {{ t('caseCard.readStory') }}
                    </button>
                </RouterLink>

                <RouterLink :to="`/donate?caseId=${caseData.id}`">
                    <button
                        class="rounded-xl bg-[#FF9800] hover:bg-[#F57C00] text-white gap-1.5 font-semibold px-4 py-2 inline-flex items-center"
                    >
                        <Heart class="w-4 h-4" />
                        {{ t('caseCard.donate') }}
                    </button>
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { RouterLink } from 'vue-router'
import { Heart, MapPin } from 'lucide-vue-next'
import ProgressRing from './ProgressRing.vue'

const { t } = useI18n()

const props = defineProps({
    caseData: {
        type: Object,
        required: true,
    },
})

const fallbackImage =
    'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400&h=300&fit=crop'

const percentage = computed(() => {
    const goal = Number(props.caseData.goal_amount || 0)
    const raised = Number(props.caseData.raised_amount || 0)

    if (goal <= 0) return 0
    return Math.min(Math.round((raised / goal) * 100), 100)
})

const remaining = computed(() => {
    return Math.max(
        Number(props.caseData.goal_amount || 0) - Number(props.caseData.raised_amount || 0),
        0
    )
})

const urgencyStyle = computed(() => {
    const urgency = props.caseData.urgency

    if (urgency === 'critical') return 'bg-red-100 text-red-700 border-red-200'
    if (urgency === 'high') return 'bg-orange-100 text-orange-700 border-orange-200'
    if (urgency === 'medium') return 'bg-blue-100 text-blue-700 border-blue-200'
    return 'bg-gray-100 text-gray-600 border-gray-200'
})

const urgencyLabel = computed(() => {
    const urgency = props.caseData.urgency

    if (urgency === 'critical') return t('caseCard.urgency.critical')
    if (urgency === 'high') return t('caseCard.urgency.high')
    if (urgency === 'medium') return t('caseCard.urgency.medium')
    return t('caseCard.urgency.active')
})

const progressStyle = computed(() => {
    let background = '#FF9800'
    if (percentage.value >= 80) background = '#4CAF50'
    else if (percentage.value >= 50) background = '#2A7DE1'

    return {
        width: `${percentage.value}%`,
        background,
    }
})

const formatMoney = (value) =>
    new Intl.NumberFormat('uz-UZ').format(value || 0) + " so'm"
</script>
