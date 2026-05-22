<template>
    <div
        :class="[
            'inline-flex items-center justify-center rounded-2xl shrink-0',
            sizeClass,
            toneClass,
        ]"
    >
        <component :is="icon" :class="iconSizeClass" aria-hidden="true" />
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    icon: {
        type: [Object, Function],
        required: true,
    },
    tone: {
        type: String,
        default: 'blue',
    },
    size: {
        type: String,
        default: 'md',
    },
})

const sizeClass = computed(() => {
    return {
        xs: 'w-8 h-8',
        sm: 'w-10 h-10',
        md: 'w-12 h-12',
        lg: 'w-14 h-14',
    }[props.size] || 'w-12 h-12'
})

const iconSizeClass = computed(() => {
    return {
        xs: 'w-4 h-4',
        sm: 'w-5 h-5',
        md: 'w-6 h-6',
        lg: 'w-7 h-7',
    }[props.size] || 'w-6 h-6'
})

const toneClass = computed(() => {
    return {
        blue: 'bg-blue-50 text-[#2A7DE1]',
        green: 'bg-green-50 text-[#4CAF50]',
        orange: 'bg-orange-50 text-[#FF9800]',
        red: 'bg-red-50 text-red-500',
        yellow: 'bg-yellow-50 text-yellow-600',
        gray: 'bg-gray-100 text-gray-600',
    }[props.tone] || 'bg-blue-50 text-[#2A7DE1]'
})
</script>
