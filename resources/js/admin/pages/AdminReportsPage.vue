<template>
    <AdminCrudShell :title="t('admin.reports')">
        <p v-if="loading" class="text-sm text-gray-500">{{ t('admin.loading') }}</p>
        <div v-for="report in reports" :key="report.id" class="rounded-lg border border-gray-100 p-3">
            <p class="font-medium">{{ report.title || report.period || `#${report.id}` }}</p>
            <p class="text-xs text-gray-500">{{ report.published_at || '-' }}</p>
        </div>
        <AdminEmptyState v-if="!loading && reports.length === 0" :message="t('admin.noData')" />
    </AdminCrudShell>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminEmptyState from '@/admin/components/common/AdminEmptyState.vue'

const { t } = useI18n()
const reports = ref([])
const loading = ref(false)

onMounted(async () => {
    loading.value = true
    try {
        const res = await api.get('/admin/reports')
        reports.value = res.data?.data || []
    } finally {
        loading.value = false
    }
})
</script>
