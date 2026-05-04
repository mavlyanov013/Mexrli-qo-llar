<template>
    <section class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="text-xl font-semibold text-gray-900">{{ title }}</h2>
        <p class="mt-2 text-sm text-gray-500">
            {{ description }}
        </p>
    </section>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
})
</script>
