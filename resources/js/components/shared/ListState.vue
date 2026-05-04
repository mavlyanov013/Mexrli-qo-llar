<template>
    <div>
        <template v-if="loading">
            <slot name="loading">
                <div class="text-sm text-gray-500">{{ loadingText }}</div>
            </slot>
        </template>
        <template v-else-if="error">
            <slot name="error">
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ error.message || errorText }}
                </div>
            </slot>
        </template>
        <template v-else-if="empty">
            <slot name="empty">
                <div class="text-sm text-gray-500">{{ emptyText }}</div>
            </slot>
        </template>
        <slot v-else />
    </div>
</template>

<script setup>
defineProps({
    loading: { type: Boolean, default: false },
    error: { type: [Object, String, null], default: null },
    empty: { type: Boolean, default: false },
    loadingText: { type: String, default: 'Loading...' },
    errorText: { type: String, default: 'Something went wrong' },
    emptyText: { type: String, default: 'No data' },
})
</script>
