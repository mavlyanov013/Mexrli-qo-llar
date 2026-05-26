<template>
    <section class="py-20 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <SectionHeader
                :title="t('urgentCases.title')"
                :subtitle="t('urgentCases.subtitle')"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <CaseCard
                    v-for="item in visibleCases"
                    :key="item.id"
                    :case-data="item"
                />
            </div>

            <div class="mt-10 text-center">
                <router-link to="/cases">
                    <button class="rounded-xl gap-2 px-8 font-semibold border-2 inline-flex items-center justify-center py-3">
                        {{ t('urgentCases.viewAll') }} →
                    </button>
                </router-link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import CaseCard from '../shared/CaseCard.vue'
import SectionHeader from '../shared/SectionHeader.vue'

const { t } = useI18n()

const props = defineProps({
    cases: {
        type: Array,
        default: () => [],
    },
})

const visibleCases = computed(() => props.cases.slice(0, 6))
</script>
