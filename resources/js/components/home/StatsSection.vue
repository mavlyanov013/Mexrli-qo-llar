<template>
    <section class="py-16 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <p class="text-center text-sm font-semibold text-[#2A7DE1] uppercase tracking-widest mb-8">
                {{ t('homeStats.topText') }}
            </p>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
                <div
                    v-for="(stat, index) in stats"
                    :key="index"
                    class="text-center"
                >
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-blue-50 flex items-center justify-center">
                        <span class="text-[#2A7DE1] text-lg">{{ stat.icon }}</span>
                    </div>

                    <AnimatedNumber
                        :target="stat.value"
                        :prefix="stat.prefix"
                        :suffix="stat.suffix"
                    />

                    <p class="text-sm text-gray-500 mt-1 font-medium">
                        {{ stat.label }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AnimatedNumber from '../shared/AnimatedNumber.vue'

const props = defineProps({
    cases: {
        type: Array,
        default: () => [],
    },
    donations: {
        type: Array,
        default: () => [],
    },
    volunteers: {
        type: Array,
        default: () => [],
    },
})

const { t } = useI18n()

const totalRaised = computed(() => {
    return props.donations.reduce((sum, item) => {
        return sum + Number(item.amount || 0)
    }, 0)
})

const activeDonorsCount = computed(() => {
    return new Set(
        props.donations
            .map((item) => item.donor_email)
            .filter(Boolean)
    ).size
})

const helpedChildrenCount = computed(() => {
    return props.cases.filter((item) =>
        ['completed', 'funded', 'closed'].includes(String(item.status || '').toLowerCase())
    ).length
})

const activeCasesCount = computed(() => {
    return props.cases.filter((item) =>
        ['active', 'open', 'in_progress'].includes(String(item.status || '').toLowerCase())
    ).length
})

const volunteersCount = computed(() => {
    return props.volunteers.filter((item) =>
        ['approved', 'interviewing', 'pending'].includes(String(item.status || '').toLowerCase())
    ).length
})

const stats = computed(() => [
    {
        icon: '💰',
        label: t('homeStats.totalRaised'),
        value: totalRaised.value,
        prefix: '',
        suffix: ' UZS',
    },
    {
        icon: '👥',
        label: t('homeStats.activeDonors'),
        value: activeDonorsCount.value,
        prefix: '',
        suffix: '+',
    },
    {
        icon: '❤',
        label: t('homeStats.childrenHelped'),
        value: helpedChildrenCount.value,
        prefix: '',
        suffix: '',
    },
    {
        icon: '💼',
        label: t('homeStats.activeProjects'),
        value: activeCasesCount.value,
        prefix: '',
        suffix: '',
    },
    {
        icon: '🤝',
        label: t('homeStats.volunteers'),
        value: volunteersCount.value,
        prefix: '',
        suffix: '+',
    },
])
</script>
