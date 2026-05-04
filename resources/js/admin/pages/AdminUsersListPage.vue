<template>
    <AdminCrudShell :title="t('admin.users')" create-to="/admin/users/create">
        <div class="mb-4 flex gap-3">
            <input
                v-model="search"
                :placeholder="t('admin.searchPlaceholder')"
                class="h-10 w-full rounded-lg border border-gray-200 px-3 text-sm"
                @input="fetchUsers(1)"
            />
        </div>

        <AdminTable :columns="columns" :rows="users">
            <template #cell-role="{ value }">
                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs text-blue-700">{{ value }}</span>
            </template>
            <template #cell-actions="{ row }">
                <div class="flex gap-2">
                    <router-link :to="`/admin/users/${row.id}`" class="text-blue-600">{{ t('admin.view') }}</router-link>
                    <router-link :to="`/admin/users/${row.id}/edit`" class="text-amber-600">{{ t('admin.edit') }}</router-link>
                    <button class="text-red-600" @click="removeUser(row.id)">{{ t('admin.delete') }}</button>
                </div>
            </template>
        </AdminTable>

        <AdminEmptyState v-if="!loading && users.length === 0" :message="t('admin.noData')" />
        <p v-if="loading" class="text-sm text-gray-500">{{ t('admin.loading') }}</p>

        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page || 1"
            :last-page="meta.last_page || 1"
            :summary="`${meta.total || 0}`"
            @change="fetchUsers"
        />
    </AdminCrudShell>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import userService from '@/services/userService'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminEmptyState from '@/admin/components/common/AdminEmptyState.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'

const { t } = useI18n()
const users = ref([])
const loading = ref(false)
const search = ref('')
const meta = ref(null)

const columns = [
    { key: 'name', label: t('admin.name') },
    { key: 'email', label: t('admin.email') },
    { key: 'role', label: t('admin.role') },
    { key: 'actions', label: t('admin.actions') },
]

const fetchUsers = async (page = 1) => {
    loading.value = true
    try {
        const res = await userService.getAll({ page, search: search.value })
        users.value = res.data || []
        meta.value = res.meta || null
    } finally {
        loading.value = false
    }
}

const removeUser = async (id) => {
    await userService.remove(id)
    await fetchUsers(meta.value?.current_page || 1)
}

onMounted(() => fetchUsers(1))
</script>
