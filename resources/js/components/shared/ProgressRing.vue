<template>
    <div class="relative inline-flex items-center justify-center">
        <svg :width="size" :height="size" class="-rotate-90">
            <circle
                :cx="size / 2"
                :cy="size / 2"
                :r="radius"
                fill="none"
                stroke="#E2E8F0"
                :stroke-width="strokeWidth"
            />
            <circle
                :cx="size / 2"
                :cy="size / 2"
                :r="radius"
                fill="none"
                :stroke="color"
                :stroke-width="strokeWidth"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="offset"
                stroke-linecap="round"
                class="transition-all duration-1000"
            />
        </svg>

        <span class="absolute text-sm font-bold" :style="{ color }">
            {{ percentage }}%
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    percentage: {
        type: Number,
        default: 0,
    },
    size: {
        type: Number,
        default: 80,
    },
    strokeWidth: {
        type: Number,
        default: 6,
    },
})

const radius = computed(() => (props.size - props.strokeWidth) / 2)
const circumference = computed(() => radius.value * 2 * Math.PI)
const offset = computed(() => {
    return circumference.value - (props.percentage / 100) * circumference.value
})

const color = computed(() => {
    if (props.percentage >= 80) return '#4CAF50'
    if (props.percentage >= 50) return '#2A7DE1'
    return '#FF9800'
})
</script>
