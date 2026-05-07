<template>
    <div class="pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <SectionHeader
                :title="t('casesPage.title')"
                :subtitle="t('casesPage.subtitle')"
            />

            <div class="mb-8 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('casesPage.searchPlaceholder')"
                        class="pl-10 rounded-xl h-11 bg-white border border-gray-300 w-full px-4 outline-none focus:ring-2 focus:ring-[#2A7DE1]"
                    />
                </div>

                <button
                    type="button"
                    class="rounded-xl gap-2 sm:hidden border border-gray-300 px-4 py-2 bg-white"
                    @click="showFilters = !showFilters"
                >
                    ⚙️ {{ t('casesPage.filters') }}
                </button>

                <div :class="showFilters ? 'flex gap-3' : 'hidden sm:flex gap-3'">
                    <select
                        v-model="urgency"
                        class="w-[140px] rounded-xl bg-white border border-gray-300 px-3 h-11 outline-none"
                    >
                        <option value="all">{{ t('casesPage.urgency.all') }}</option>
                        <option value="critical">{{ t('casesPage.urgency.critical') }}</option>
                        <option value="high">{{ t('casesPage.urgency.high') }}</option>
                        <option value="medium">{{ t('casesPage.urgency.medium') }}</option>
                        <option value="low">{{ t('casesPage.urgency.low') }}</option>
                    </select>

                    <select
                        v-model="category"
                        class="w-[150px] rounded-xl bg-white border border-gray-300 px-3 h-11 outline-none"
                    >
                        <option value="all">{{ t('casesPage.categories.all') }}</option>
                        <option value="disability">{{ t('casesPage.categories.disability') }}</option>
                        <option value="illness">{{ t('casesPage.categories.illness') }}</option>
                        <option value="family_support">{{ t('casesPage.categories.family_support') }}</option>
                        <option value="education">{{ t('casesPage.categories.education') }}</option>
                        <option value="medical">{{ t('casesPage.categories.medical') }}</option>
                        <option value="rehabilitation">{{ t('casesPage.categories.rehabilitation') }}</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="i in 3"
                    :key="i"
                    class="bg-white rounded-2xl overflow-hidden animate-pulse"
                >
                    <div class="aspect-square bg-gray-200" />
                    <div class="p-5 space-y-3">
                        <div class="h-5 bg-gray-200 rounded w-2/3" />
                        <div class="h-4 bg-gray-100 rounded w-full" />
                        <div class="h-2 bg-gray-100 rounded w-full" />
                    </div>
                </div>
            </div>

            <div v-else-if="filteredCases.length === 0" class="text-center py-20">
                <div class="text-5xl text-gray-300 mb-4">🔎</div>
                <p class="text-gray-500">{{ t('casesPage.empty') }}</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <CaseCard
                    v-for="item in filteredCases"
                    :key="item.id"
                    :case-data="item"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import caseService from '../services/caseService'
import CaseCard from '../components/shared/CaseCard.vue'
import SectionHeader from '../components/shared/SectionHeader.vue'

const { t } = useI18n()

const cases = ref([])
const loading = ref(false)

const search = ref('')
const urgency = ref('all')
const category = ref('all')
const showFilters = ref(false)

const fetchCases = async () => {
    loading.value = true

    const result = await caseService.fetchList()
    cases.value = result.data || []
    loading.value = false
}

const filteredCases = computed(() => {
    return cases.value.filter((item) => {
        const query = search.value.trim().toLowerCase()

        const matchSearch =
            !query ||
            item.name?.toLowerCase().includes(query) ||
            item.short_description?.toLowerCase().includes(query) ||
            item.condition?.toLowerCase().includes(query)

        const matchUrgency =
            urgency.value === 'all' || item.urgency === urgency.value

        const matchCategory =
            category.value === 'all' || item.category === category.value

        return matchSearch && matchUrgency && matchCategory
    })
})

onMounted(fetchCases)
</script>
