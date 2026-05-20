<template>
    <span
        class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
        :class="toneClass"
    >
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
    status: { type: String, default: '' },
    map: { type: Object, required: true },
})

const { t } = useI18n()

const toneClasses = {
    success: 'bg-green-50 text-green-700',
    warning: 'bg-yellow-50 text-yellow-700',
    danger: 'bg-red-50 text-red-700',
    info: 'bg-blue-50 text-blue-700',
    neutral: 'bg-gray-100 text-gray-700',
}

const meta = computed(() =>
    props.map?.[String(props.status || '').toLowerCase()] || null
)

const toneClass = computed(() =>
    toneClasses[meta.value?.tone] || toneClasses.neutral
)

// 🔥 FIX HERE
const label = computed(() => {
    if (!meta.value) return props.status || t('common.unknown')

    // i18n system
    if (meta.value.labelKey) {
        return t(meta.value.labelKey)
    }

    // static label system (seniki)
    if (meta.value.label) {
        return meta.value.label
    }

    return props.status
})
</script>
