<template>
    <AdminCrudShell :title="title">

        <!-- FILTER BAR -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">

            <input
                v-model="filters.search"
                placeholder="qidirish xayriya..."
                class="h-10 border rounded-lg px-3 text-sm"
            />

            <select v-model="filters.status" class="h-10 border rounded-lg px-3 text-sm">
                <option value="">Barcha holatlar</option>
                <option value="pending">pending</option>
                <option value="completed">completed</option>
                <option value="failed">failed</option>
            </select>

            <button
                class="h-10 bg-gray-900 text-white rounded-lg text-sm"
                @click="resetFilters"
            >
                Reset
            </button>

        </div>

        <!-- TABLE -->
        <ListState :loading="loading" :error="error" :empty="filteredDonations.length === 0">

            <AdminTable :columns="columns" :rows="filteredDonations">

                <template #cell-status="{ row }">
                    <StatusBadge :status="row.status" :map="DONATION_STATUSES" />
                </template>

            </AdminTable>

        </ListState>

    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDonations } from '@/composables/useDonations'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { DONATION_STATUSES } from '@/constants/statuses'

const { t } = useI18n()
const { donations, loading, error, fetchDonations } = useDonations()

const filters = reactive({
    search: '',
    status: '',
    type: '',
})

onMounted(async () => {
    await fetchDonations({ admin: true })
})

const filteredDonations = computed(() => {
    return donations.value.filter(item => {
        const matchSearch =
            !filters.search ||
            item.donor_name?.toLowerCase().includes(filters.search.toLowerCase())

        const matchStatus =
            !filters.status || item.status === filters.status

        const matchType =
            !filters.type || item.type === filters.type

        return matchSearch && matchStatus && matchType
    })
})

const resetFilters = () => {
    filters.search = ''
    filters.status = ''
    filters.type = ''
}

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'donor_name', label: t('admin.name') },
    { key: 'amount', label: t('admin.amount') },
    { key: 'status', label: t('admin.status') },
]

const title = t('admin.donations')
</script>
