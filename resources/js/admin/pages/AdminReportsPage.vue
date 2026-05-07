<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/reports/create' : ''">
        <template v-if="isListMode">
        <ListState :loading="loading" :error="error" :empty="reports.length === 0">
            <AdminTable :columns="columns" :rows="reports">
                <template #cell-actions="{ row }">
                    <div class="flex gap-2 text-sm">
                        <router-link class="text-slate-600" :to="`/admin/reports/${row.id}`">view</router-link>
                        <router-link class="text-amber-600" :to="`/admin/reports/${row.id}/edit`">edit</router-link>
                        <button class="text-red-600" @click="remove(row.id)">delete</button>
                    </div>
                </template>
            </AdminTable>
        </ListState>
        </template>

        <template v-else-if="isViewMode && current">
            <div class="space-y-2 text-sm">
                <p><strong>{{ t('admin.title') }}:</strong> {{ current.title }}</p>
                <p><strong>{{ t('admin.type') }}:</strong> {{ current.type }}</p>
                <p><strong>{{ t('admin.period') }}:</strong> {{ current.period }}</p>
                <p><strong>{{ t('admin.received') }}:</strong> {{ current.total_received }}</p>
                <p><strong>{{ t('admin.spent') }}:</strong> {{ current.total_spent }}</p>
            </div>
        </template>

        <template v-else>
            <form class="grid grid-cols-1 gap-3 md:grid-cols-2" @submit.prevent="save">
                <input v-model="form.title" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" :placeholder="t('admin.title')" required />
                <input v-model="form.period" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" :placeholder="t('admin.period')" required />
                <select v-model="form.type" class="h-10 rounded-lg border border-gray-200 px-3 text-sm">
                    <option value="quarterly">quarterly</option>
                    <option value="yearly">yearly</option>
                    <option value="audit">audit</option>
                </select>
                <input v-model.number="form.total_received" type="number" min="0" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" :placeholder="t('admin.received')" />
                <input v-model.number="form.total_spent" type="number" min="0" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" :placeholder="t('admin.spent')" />
                <input v-model="form.document_url" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" :placeholder="t('admin.fileUrl')" />
                <div class="md:col-span-2 flex gap-2">
                    <button class="rounded-lg bg-[#2A7DE1] px-4 py-2 text-sm text-white">{{ t('admin.save') }}</button>
                    <router-link to="/admin/reports" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">{{ t('admin.cancel') }}</router-link>
                </div>
            </form>
        </template>
    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useReports } from '@/composables/useReports'
import reportService from '@/services/reportService'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import ListState from '@/components/shared/ListState.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const { reports, loading, error, fetchReports, createReport, updateReport, deleteReport } = useReports()
const current = ref(null)
const isListMode = computed(() => route.name === 'admin-reports')
const isEditMode = computed(() => route.name === 'admin-reports-edit')
const isViewMode = computed(() => route.name === 'admin-reports-view')
const title = computed(() => isListMode.value ? t('admin.reports') : isEditMode.value ? 'Edit report' : route.name === 'admin-reports-create' ? 'Create report' : 'Report details')
const form = reactive({
    title: '',
    period: '',
    type: 'quarterly',
    total_received: 0,
    total_spent: 0,
    document_url: '',
})
const columns = [
    { key: 'id', label: 'ID' },
    { key: 'title', label: t('admin.title') },
    { key: 'type', label: t('admin.type') },
    { key: 'period', label: t('admin.period') },
    { key: 'actions', label: t('admin.actions') },
]
const loadCurrent = async () => {
    const result = await reportService.getById(route.params.id)
    current.value = result.data
    if (isEditMode.value && result.data) {
        form.title = result.data.title || ''
        form.period = result.data.period || ''
        form.type = result.data.type || 'quarterly'
        form.total_received = Number(result.data.total_received || 0)
        form.total_spent = Number(result.data.total_spent || 0)
        form.document_url = result.data.document_url || ''
    }
}
const save = async () => {
    const payload = { ...form }
    const result = isEditMode.value ? await updateReport(route.params.id, payload) : await createReport(payload)
    if (!result.error) {
        router.push('/admin/reports')
    }
}
const remove = async (id) => {
    if (!window.confirm('Delete this report?')) return
    const result = await deleteReport(id)
    if (!result.error) await fetchReports({ admin: true })
}

onMounted(async () => {
    if (isListMode.value) await fetchReports({ admin: true })
    else await loadCurrent()
})
</script>
