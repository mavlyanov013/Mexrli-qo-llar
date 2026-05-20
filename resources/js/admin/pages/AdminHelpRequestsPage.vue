<template>
    <AdminCrudShell title="Yordam so‘rovlari">
        <AdminTable :columns="columns" :rows="rows">
            <template #cell-category="{ row }">
                {{ HELP_REQUEST_CATEGORIES[row.category] ?? row.category }}
            </template>

            <template #cell-status="{ row }">
                <StatusBadge
                    :status="row.status"
                    :map="HELP_REQUEST_STATUS"
                />
            </template>
            <template #cell-actions="{ row }">
                <div class="flex items-center gap-3">
                    <button
                        class="p-2 rounded-md hover:bg-green-50 text-green-600 disabled:opacity-40"
                        :disabled="row.status === 'approved'"
                        title="Tasdiqlash"
                        @click="approve(row.id)"
                    >
                        ✔
                    </button>
                    <button
                        class="p-2 rounded-md hover:bg-red-50 text-red-600 disabled:opacity-40"
                        :disabled="row.status === 'rejected'"
                        title="Rad etish"
                        @click="reject(row.id)"
                    >
                        ❌
                    </button>
                </div>
            </template>
        </AdminTable>
    </AdminCrudShell>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import helpRequestService from '@/services/helpRequestService'
import StatusBadge from "../../components/shared/StatusBadge.vue";
import {HELP_REQUEST_CATEGORIES, HELP_REQUEST_STATUS} from "../../constants/statuses.js";

const rows = ref([])
const columns = [
    { key: 'full_name', label: 'F.I.SH' },
    { key: 'phone', label: 'Telefon' },
    { key: 'category', label: 'Kategoriya' },
    { key: 'status', label: 'Holat' },
    { key: 'created_at', label: 'Sana' },
    { key: 'actions', label: 'Amallar' },
]

const load = async () => {
    rows.value = await helpRequestService.getAll()
}

const approve = async (id) => {
    await helpRequestService.approve(id)
    await load()
}

const reject = async (id) => {
    await helpRequestService.reject(id)
    await load()
}

onMounted(load)
</script>
