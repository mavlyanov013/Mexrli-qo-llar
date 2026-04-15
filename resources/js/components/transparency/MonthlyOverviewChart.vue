<template>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <!-- Title -->
        <h3 class="font-bold text-gray-900 text-lg mb-6">
            {{ t('transparencyPage.monthlyOverview') }}
        </h3>

        <!-- Chart -->
        <div class="flex gap-6 h-80">
            <!-- Y axis -->
            <div class="flex flex-col justify-between text-xs text-gray-400 pr-2">
                <span>$180k</span>
                <span>$135k</span>
                <span>$90k</span>
                <span>$45k</span>
                <span>$0</span>
            </div>

            <!-- Bars -->
            <div class="flex-1 flex items-end justify-between">
                <div
                    v-for="item in monthlyData"
                    :key="item.key"
                    class="flex flex-col items-center gap-2 w-full"
                >
                    <!-- bars -->
                    <div class="flex items-end gap-1 h-56">
                        <!-- Received -->
                        <div
                            class="w-6 md:w-8 rounded-t-xl bg-[#2A7DE1]"
                            :style="{ height: item.receivedHeight + '%' }"
                        />

                        <!-- Spent -->
                        <div
                            class="w-6 md:w-8 rounded-t-xl bg-[#4CAF50]"
                            :style="{ height: item.spentHeight + '%' }"
                        />
                    </div>

                    <!-- month -->
                    <span class="text-xs text-gray-500 font-medium">
                        {{ item.label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center justify-center gap-6 mt-6 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-[#2A7DE1]" />
                <span class="text-gray-600">
                    {{ t('transparencyPage.received') }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-[#4CAF50]" />
                <span class="text-gray-600">
                    {{ t('transparencyPage.spent') }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

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

const monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']

const monthlyData = computed(() => {
    const now = new Date()
    const months = []

    // last 6 months
    for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1)

        months.push({
            key: `${d.getFullYear()}-${d.getMonth()}`,
            label: monthLabels[d.getMonth()],
            received: 0,
            spent: 0,
        })
    }

    // group donations
    for (const d of props.donations || []) {
        const raw = d.created_at || d.created_date
        if (!raw) continue

        const date = new Date(raw)
        const key = `${date.getFullYear()}-${date.getMonth()}`
        const found = months.find(m => m.key === key)

        if (found) {
            // 💡 received
            found.received += Number(d.amount || 0)

            // 💡 spent (fake for now → later financial report bilan almashtiramiz)
            found.spent += Number(d.amount || 0) * 0.85
        }
    }

    const max = Math.max(
        ...months.map(m => Math.max(m.received, m.spent)),
        1
    )

    return months.map(m => ({
        ...m,
        receivedHeight: (m.received / max) * 100,
        spentHeight: (m.spent / max) * 100,
    }))
})
</script>
