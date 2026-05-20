<template>
    <section class="py-16 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <p class="text-center text-sm font-semibold text-[#2A7DE1] uppercase tracking-widest mb-8">
                {{ t('homeStats.topText') }}
            </p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div
                    v-for="(stat, index) in stats"
                    :key="index"
                    class="flex flex-col items-center text-center"
                >
                    <IconBadge
                        :icon="stat.icon"
                        :tone="stat.tone"
                        size="lg"
                        class="mb-4"
                    />

                    <div class="leading-none">
                        <AnimatedNumber
                            :target="stat.value"
                            :prefix="stat.prefix"
                            :suffix="stat.suffix"
                        />
                    </div>

                    <p class="text-sm text-gray-500 mt-3 font-medium">
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
import { Wallet, Users, HeartHandshake, BriefcaseBusiness } from 'lucide-vue-next'
import AnimatedNumber from '../shared/AnimatedNumber.vue'
import IconBadge from '../shared/IconBadge.vue'

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
    posts: {
        type: Array,
        default: () => [],
    },
})

const { t } = useI18n()

const totalRaised = computed(() => {
    return props.donations.reduce((sum, item) => sum + Number(item.amount || 0), 0)
})

const activeDonorsCount = computed(() => {
    return new Set(
        props.donations
            .map((item) => `${item?.donor_name || ''}-${item?.donor_phone || ''}`)
            .filter(Boolean)
    ).size
})

const helpedChildrenCount = computed(() => {
    return props.posts.filter(item =>
        item.category === 'success_story' &&
        item.status === 'published'
    ).length
})

const activeCasesCount = computed(() => {
    return props.cases.filter((item) =>
        ['active', 'open', 'in_progress'].includes(
            String(item?.status || '').toLowerCase()
        )
    ).length
})

const stats = computed(() => [
    {
        icon: Wallet,
        tone: 'blue',
        label: t('homeStats.totalRaised'),
        value: totalRaised.value,
        prefix: '',
        suffix: ' UZS',
    },
    {
        icon: Users,
        tone: 'blue',
        label: t('homeStats.activeDonors'),
        value: activeDonorsCount.value,
        prefix: '',
        suffix: '+',
    },
    {
        icon: HeartHandshake,
        tone: 'red',
        label: t('homeStats.childrenHelped'),
        value: helpedChildrenCount.value,
        prefix: '',
        suffix: '',
    },
    {
        icon: BriefcaseBusiness,
        tone: 'orange',
        label: t('homeStats.activeProjects'),
        value: activeCasesCount.value,
        prefix: '',
        suffix: '',
    },
])
</script>
