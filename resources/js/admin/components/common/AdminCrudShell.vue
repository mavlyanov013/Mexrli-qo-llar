<template>
    <section class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ title }}</h2>
                <p v-if="subtitle" class="mt-1 text-sm text-gray-500 max-w-2xl">
                    {{ subtitle }}
                </p>
            </div>
            <router-link
                v-if="createTo"
                :to="createTo"
                class="rounded-lg bg-[#2A7DE1] px-4 py-2 text-sm font-medium text-white"
            >
                ➕ {{ t('admin.create') }}
            </router-link>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <slot v-if="$slots.default" />
        </div>
    </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    createTo: { type: String, default: '' },
})

const { t } = useI18n()
</script>
