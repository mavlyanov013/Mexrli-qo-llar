<template>
    <section class="relative min-h-[90vh] flex items-center overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/95 to-transparent z-10" />

        <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[55%]">
            <img
                :src="featuredCase?.photo_url || heroImage"
                :alt="t('hero.imageAlt')"
                class="w-full h-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-r from-white via-white/40 to-transparent" />
        </div>

        <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 w-full">
            <div class="max-w-xl">
                <div class="transition-all duration-700 ease-out">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-gray-900 leading-tight">
                        {{ headline }}
                    </h1>

                    <p class="mt-5 text-lg text-gray-600 leading-relaxed max-w-md">
                        {{ story }}
                    </p>

                    <div
                        v-if="featuredCase"
                        class="mt-4 bg-orange-50 border border-orange-100 rounded-xl p-4"
                    >
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">{{ t('hero.raised') }}</span>
                            <span class="font-bold text-gray-900">
                                ${{ formatNumber(featuredCase.raised_amount || 0) }}
                                {{ t('hero.of') }}
                                ${{ formatNumber(featuredCase.goal_amount || 0) }}
                            </span>
                        </div>

                        <div class="w-full h-2.5 bg-orange-100 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full bg-[#FF9800]"
                                :style="{ width: `${progressPercent}%` }"
                            />
                        </div>

                        <p class="text-xs text-orange-600 mt-2 font-medium">
                            ${{ formatNumber(remainingAmount) }} {{ t('hero.stillNeeded') }}
                        </p>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <router-link :to="donateLink">
                            <button class="bg-[#FF9800] hover:bg-[#F57C00] text-white rounded-xl gap-2 px-7 h-13 text-base font-semibold shadow-lg shadow-orange-200/50 inline-flex items-center justify-center">
                                {{ t('hero.donateNow') }}
                            </button>
                        </router-link>

                        <router-link to="/help-request">
                            <button class="rounded-xl gap-2 px-7 h-13 text-base font-semibold border-2 inline-flex items-center justify-center">
                                {{ t('hero.requestHelp') }}
                            </button>
                        </router-link>
                    </div>

                    <div class="mt-5 flex items-center gap-4 text-sm text-gray-500 flex-wrap">
                        <span class="flex items-center gap-1.5">
                            <span class="text-[#4CAF50]">✓</span>
                            {{ t('hero.transparentSystem') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="text-[#2A7DE1]">✓</span>
                            {{ t('hero.trustedDonors') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    heroImage: {
        type: String,
        required: true,
    },
    featuredCase: {
        type: Object,
        default: null,
    },
})

const headline = computed(() => {
    if (props.featuredCase) {
        return t('hero.featuredHeadline', {
            name: props.featuredCase.name,
            age: props.featuredCase.age,
        })
    }

    return t('hero.defaultHeadline')
})

const story = computed(() => {
    return props.featuredCase?.short_description || t('hero.defaultStory')
})

const progressPercent = computed(() => {
    if (!props.featuredCase?.goal_amount) return 0

    return Math.min(
        Math.round(
            ((props.featuredCase.raised_amount || 0) / props.featuredCase.goal_amount) * 100
        ),
        100
    )
})

const remainingAmount = computed(() => {
    if (!props.featuredCase?.goal_amount) return 0
    return Math.max(props.featuredCase.goal_amount - (props.featuredCase.raised_amount || 0), 0)
})

const donateLink = computed(() => {
    if (props.featuredCase?.id) {
        return `/donate?caseId=${props.featuredCase.id}`
    }

    return '/donate'
})

const formatNumber = (value) => {
    return Number(value || 0).toLocaleString()
}
</script>
