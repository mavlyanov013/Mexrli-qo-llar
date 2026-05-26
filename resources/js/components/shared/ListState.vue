<template>
    <div>
        <template v-if="loading">
            <slot name="loading">
                <div class="text-sm text-gray-500">{{ resolvedLoadingText }}</div>
            </slot>
        </template>
        <template v-else-if="error">
            <slot name="error">
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ error.message || resolvedErrorText }}
                </div>
            </slot>
        </template>
        <template v-else-if="empty">
            <slot name="empty">
                <div class="text-sm text-gray-500">{{ resolvedEmptyText }}</div>
            </slot>
        </template>
        <slot v-else />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
    loading: { type: Boolean, default: false },
    error: { type: [Object, String, null], default: null },
    empty: { type: Boolean, default: false },
    loadingText: { type: String, default: '' },
    errorText: { type: String, default: '' },
    emptyText: { type: String, default: '' },
})

const { t } = useI18n()

const resolvedLoadingText = computed(() => props.loadingText || t('common.listState.loading'))
const resolvedErrorText = computed(() => props.errorText || t('common.listState.error'))
const resolvedEmptyText = computed(() => props.emptyText || t('common.listState.empty'))
</script>
