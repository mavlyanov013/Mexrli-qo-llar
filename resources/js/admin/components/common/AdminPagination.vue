<template>
    <div
        v-if="lastPage > 1 || summary"
        class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/60 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-sm text-gray-600">
            {{ summary || `${currentPage}-sahifa, jami ${lastPage} sahifa` }}
        </p>

        <div class="flex items-center gap-2">
            <button
                type="button"
                class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-[#2A7DE1]/30 hover:text-[#2A7DE1] disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="currentPage <= 1"
                @click="$emit('change', currentPage - 1)"
            >
                {{ previousLabel }}
            </button>

            <span class="min-w-[4.5rem] text-center text-sm font-semibold text-gray-800">
                {{ currentPage }} / {{ lastPage }}
            </span>

            <button
                type="button"
                class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-[#2A7DE1]/30 hover:text-[#2A7DE1] disabled:cursor-not-allowed disabled:opacity-40"
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
    previousLabel: { type: String, default: 'Oldingi' },
    nextLabel: { type: String, default: 'Keyingi' },
})
</script>
