<template>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h3 class="font-bold text-gray-900 text-lg">
                {{ t('transparencyPage.monthlyOverview') }}
            </h3>

            <div class="flex items-center gap-2">
                <label for="chart-year" class="text-sm text-gray-500">
                    {{ t('transparencyPage.year') }}
                </label>
                <select
                    id="chart-year"
                    v-model.number="selectedYear"
                    class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-800 bg-white outline-none focus:border-[#2A7DE1] focus:ring-2 focus:ring-[#2A7DE1]/20"
                    :disabled="loading"
                    @change="loadChart"
                >
                    <option v-for="year in yearOptions" :key="year" :value="year">
                        {{ year }}
                    </option>
                </select>
            </div>
        </div>

        <div v-if="loading" class="h-80 flex items-center justify-center text-sm text-gray-500">
            {{ t('transparencyPage.chartLoading') }}
        </div>

        <div v-else-if="error" class="h-80 flex items-center justify-center text-sm text-red-600">
            {{ error }}
        </div>

        <template v-else-if="monthlyData.length">
            <div class="flex gap-6 h-80">
                <div class="flex flex-col justify-between text-xs text-gray-400 pr-2 shrink-0">
                    <span v-for="(tick, index) in yAxisTicks" :key="index">{{ tick }}</span>
                </div>

                <div class="flex-1 flex items-end justify-between gap-1 min-w-0">
                    <div
                        v-for="item in monthlyData"
                        :key="item.month"
                        class="flex flex-col items-center gap-2 flex-1 min-w-0"
                    >
                        <div class="relative flex items-end justify-center gap-1 h-56 w-full">
                            <div
                                class="relative"
                                @mouseenter="setTooltip(item, 'received')"
                                @mouseleave="clearTooltip"
                            >
                                <div
                                    class="w-4 sm:w-5 md:w-6 rounded-t-xl bg-[#2A7DE1] cursor-pointer transition-opacity hover:opacity-90"
                                    :style="barStyle(item.receivedHeight)"
                                />
                                <div
                                    v-if="isTooltipActive(item, 'received')"
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-20 pointer-events-none"
                                >
                                    <div class="rounded-lg bg-gray-900 text-white text-xs px-3 py-2 shadow-lg whitespace-nowrap">
                                        <p class="font-semibold">{{ item.label }} — {{ t('transparencyPage.received') }}</p>
                                        <p>{{ formatMoney(item.received) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="relative"
                                @mouseenter="setTooltip(item, 'spent')"
                                @mouseleave="clearTooltip"
                            >
                                <div
                                    class="w-4 sm:w-5 md:w-6 rounded-t-xl bg-[#4CAF50] cursor-pointer transition-opacity hover:opacity-90"
                                    :style="barStyle(item.spentHeight)"
                                />
                                <div
                                    v-if="isTooltipActive(item, 'spent')"
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-20 pointer-events-none"
                                >
                                    <div class="rounded-lg bg-gray-900 text-white text-xs px-3 py-2 shadow-lg whitespace-nowrap">
                                        <p class="font-semibold">{{ item.label }} — {{ t('transparencyPage.spent') }}</p>
                                        <p>{{ formatMoney(item.spent) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <span class="text-xs text-gray-500 font-medium truncate w-full text-center">
                            {{ item.label }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center gap-6 mt-6 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#2A7DE1]" />
                    <span class="text-gray-600">{{ t('transparencyPage.received') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#4CAF50]" />
                    <span class="text-gray-600">{{ t('transparencyPage.spent') }}</span>
                </div>
            </div>
        </template>

        <div v-else class="h-80 flex items-center justify-center text-sm text-gray-500">
            {{ t('transparencyPage.noChartData') }}
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import transparencyService from '@/services/transparencyService'

const props = defineProps({
    t: {
        type: Function,
        required: true,
    },
})

const CHART_HEIGHT_PX = 224

const currentYear = new Date().getFullYear()
const yearOptions = Array.from({ length: 5 }, (_, index) => currentYear - index)

const selectedYear = ref(currentYear)
const chartPayload = ref(null)
const loading = ref(true)
const error = ref('')
const activeTooltip = ref(null)

const formatMoney = (value) => {
    return `${Number(value || 0).toLocaleString()} so'm`
}

const barStyle = (percent) => {
    const height = Math.max(0, Math.min(100, Number(percent) || 0))
    const px = height > 0 ? Math.max(4, (height / 100) * CHART_HEIGHT_PX) : 0

    return {
        height: `${px}px`,
    }
}

const yAxisTicks = computed(() => {
    const max = chartPayload.value?.max_value || 1
    const steps = 4

    return Array.from({ length: steps + 1 }, (_, index) => {
        const value = (max / steps) * (steps - index)
        return formatMoney(value)
    })
})

const monthlyData = computed(() => {
    const months = chartPayload.value?.months || []
    const max = chartPayload.value?.max_value || 1

    return months.map((month) => ({
        ...month,
        receivedHeight: (month.received / max) * 100,
        spentHeight: (month.spent / max) * 100,
    }))
})

const setTooltip = (item, type) => {
    activeTooltip.value = `${item.month}-${type}`
}

const clearTooltip = () => {
    activeTooltip.value = null
}

const isTooltipActive = (item, type) => {
    return activeTooltip.value === `${item.month}-${type}`
}

const loadChart = async () => {
    loading.value = true
    error.value = ''

    try {
        chartPayload.value = await transparencyService.fetchChart(selectedYear.value)
    } catch (err) {
        console.error('Transparency chart load error:', err)
        error.value = props.t('transparencyPage.chartError')
        chartPayload.value = null
    } finally {
        loading.value = false
    }
}

onMounted(loadChart)
</script>
