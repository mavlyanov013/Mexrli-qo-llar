<template>
    <div class="flex items-center justify-between border-t border-gray-100 p-4">
        <p class="text-sm text-gray-500">
            {{ summary }}
        </p>

        <div class="flex items-center gap-2">
            <button
                type="button"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-700 disabled:opacity-50"
                :disabled="currentPage <= 1"
                @click="$emit('change', currentPage - 1)"
            >
                {{ previousLabel }}
            </button>
            <button
                type="button"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-700 disabled:opacity-50"
                :disabled="currentPage >= lastPage"
                @click="$emit('change', currentPage + 1)"
            >
                {{ nextLabel }}
            </button>
        </div>
    </div>
</template>

<script setup>
defineEmits(['change'])

defineProps({
    currentPage: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
    summary: { type: String, default: '' },
    previousLabel: { type: String, default: 'Previous' },
    nextLabel: { type: String, default: 'Next' },
})
</script>
