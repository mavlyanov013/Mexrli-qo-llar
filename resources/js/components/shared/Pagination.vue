<template>
    <div
        v-if="lastPage > 1"
        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        :class="wrapperClass"
    >
        <p v-if="showSummary" class="text-sm text-gray-500">
            {{ summaryText }}
        </p>

        <div class="flex items-center gap-2 flex-wrap">
            <button
                type="button"
                class="rounded-xl px-4 py-2 text-sm border border-gray-300 bg-white text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors hover:border-[#2A7DE1] hover:text-[#2A7DE1]"
                :disabled="currentPage <= 1"
                @click="$emit('prev')"
            >
                {{ previousLabel }}
            </button>

            <div class="flex items-center gap-2 flex-wrap">
                <button
                    v-for="page in visiblePages"
                    :key="page"
                    type="button"
                    class="min-w-10 h-10 rounded-xl text-sm border transition-all"
                    :class="page === currentPage
                        ? 'bg-[#2A7DE1] text-white border-[#2A7DE1]'
                        : 'bg-white text-gray-700 border-gray-300 hover:border-[#2A7DE1] hover:text-[#2A7DE1]'"
                    @click="$emit('change', page)"
                >
                    {{ page }}
                </button>
            </div>

            <button
                type="button"
                class="rounded-xl px-4 py-2 text-sm border border-gray-300 bg-white text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors hover:border-[#2A7DE1] hover:text-[#2A7DE1]"
                :disabled="currentPage >= lastPage"
                @click="$emit('next')"
            >
                {{ nextLabel }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
    currentPage: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
    startItem: { type: Number, default: 0 },
    endItem: { type: Number, default: 0 },
    totalItems: { type: Number, default: 0 },
    visiblePages: { type: Array, default: () => [] },
    wrapperClass: { type: String, default: '' },
    showSummary: { type: Boolean, default: true },
    previousLabel: { type: String, default: '' },
    nextLabel: { type: String, default: '' },
})

defineEmits(['change', 'prev', 'next'])

const { t } = useI18n()

const summaryText = computed(() => {
    if (!props.totalItems) return ''
    return t('common.pagination.range', {
        start: props.startItem,
        end: props.endItem,
        total: props.totalItems,
    })
})

const previousLabel = computed(() => props.previousLabel || t('common.pagination.prev'))
const nextLabel = computed(() => props.nextLabel || t('common.pagination.next'))
</script>
