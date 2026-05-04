<template>
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-lg">
                {{ t('transparencyPage.publishedReports') }}
            </h3>
        </div>

        <div v-if="reports.length" class="divide-y divide-gray-100">
            <div
                v-for="report in reports"
                :key="report.id"
                class="p-6 flex items-center justify-between gap-4 flex-wrap"
            >
                <div class="flex items-center gap-4 min-w-0">
                    <IconBadge :icon="FileText" tone="blue" size="md" />

                    <div class="min-w-0">
                        <p class="font-bold text-gray-900 text-lg">
                            {{ report.title }}
                        </p>
                        <p class="text-gray-500">
                            {{ report.period }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <span class="inline-flex rounded-full bg-gray-100 text-gray-600 px-3 py-1 text-xs font-medium">
                        {{ t(`transparencyPage.reportTypes.${report.typeKey}`) }}
                    </span>

                    <ExternalLink
                        :href="report.file_url"
                        classes="inline-flex items-center gap-2 rounded-xl px-5 py-3 border border-gray-300 bg-white text-gray-900 font-medium hover:bg-gray-50"
                    >
                        <Download class="w-4 h-4" />
                        {{ t('transparencyPage.downloadReport') }}
                    </ExternalLink>
                </div>
            </div>
        </div>

        <div v-else class="p-6 text-gray-500">
            {{ t('transparencyPage.noReports') }}
        </div>
    </div>
</template>

<script setup>
import { Download, FileText } from 'lucide-vue-next'
import IconBadge from '../shared/IconBadge.vue'
import ExternalLink from '../shared/ExternalLink.vue'

defineProps({
    reports: {
        type: Array,
        default: () => [],
    },
    t: {
        type: Function,
        required: true,
    },
})
</script>
