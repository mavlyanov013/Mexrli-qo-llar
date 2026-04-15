<template>
    <div :class="['mb-12', align === 'center' ? 'text-center' : 'text-left']">
        <h2
            :class="[
                'text-3xl md:text-4xl font-bold tracking-tight',
                light ? 'text-white' : 'text-gray-900'
            ]"
        >
            {{ title }}
        </h2>

        <p
            v-if="subtitle"
            :class="[
                'mt-3 text-lg max-w-2xl',
                align === 'center' ? 'mx-auto' : '',
                light ? 'text-white/80' : 'text-gray-500'
            ]"
        >
            {{ subtitle }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    align: {
        type: String,
        default: 'center',
    },
    light: {
        type: Boolean,
        default: false,
    },
})
</script>
