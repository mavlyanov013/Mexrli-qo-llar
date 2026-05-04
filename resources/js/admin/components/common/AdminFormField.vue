<template>
    <div class="space-y-2">
        <label
            v-if="label"
            :for="id"
            class="text-sm font-medium text-gray-700"
        >
            {{ label }}
        </label>

        <slot />

        <p
            v-if="error"
            class="text-xs text-red-600"
        >
            {{ error }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    id: { type: String, default: '' },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
})
</script>
