<template>
    <AdminCrudShell
        :title="t('admin.users')"
        :create-to="isSuperAdmin ? '/admin/users/create' : null"
    >
        <p v-if="loadError" class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ loadError }}
        </p>

        <!-- SEARCH -->
        <div class="mb-5 flex items-center gap-3">
            <Search class="w-4 h-4 text-gray-400" />

            <input
                v-model="search"
                :placeholder="t('admin.searchPlaceholder')"
                class="h-10 w-full rounded-xl border border-gray-200 px-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none"
                @input="fetchUsers(1)"
            />
        </div>

        <!-- TABLE -->
        <AdminTable :columns="columns" :rows="users">

            <template #cell-role="{ value }">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs text-blue-700">
                    <Shield class="w-3 h-3" />
                    {{ value }}
                </span>
            </template>

            <template #cell-actions="{ row }">
                <div class="flex items-center gap-2">

                    <!-- KO‘RISH -->
                    <router-link
                        :to="`/admin/users/${row.id}`"
                        class="p-2 rounded-md hover:bg-blue-50 text-blue-600"
                        title="Ko‘rish"
                    >
                        <Eye class="w-5 h-5" />
                    </router-link>

                    <!-- EDIT -->
                    <router-link
                        :to="`/admin/users/${row.id}/edit`"
                        class="p-2 rounded-md hover:bg-amber-50 text-amber-600"
                        title="Tahrirlash"
                    >
                        <Pencil class="w-5 h-5" />
                    </router-link>

                    <!-- DELETE -->
                    <button
                        @click="removeUser(row.id)"
                        class="p-2 rounded-md hover:bg-red-50 text-red-600"
                        title="O‘chirish"
                    >
                        <Trash2 class="w-5 h-5" />
                    </button>

                </div>
            </template>
        </AdminTable>

        <!-- EMPTY -->
        <AdminEmptyState
            v-if="!loading && users.length === 0"
            :message="t('admin.noData')"
        />

        <!-- LOADING -->
        <div v-if="loading" class="flex items-center gap-2 text-sm text-gray-500 mt-4">
            <Loader2 class="w-4 h-4 animate-spin" />
            {{ t('admin.loading') }}
        </div>

        <!-- PAGINATION -->
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
import { usePermissions } from '@/composables/usePermissions'


import {
    Search,
    Shield,
    Eye,
    Pencil,
    Trash2,
    Loader2
} from 'lucide-vue-next'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminEmptyState from '@/admin/components/common/AdminEmptyState.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'

const { t } = useI18n()
const { isSuperAdmin } = usePermissions()

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

const loadError = ref(null)

const fetchUsers = async (page = 1) => {
    loading.value = true
    loadError.value = null
    try {
        const res = await userService.getAll({ page, search: search.value })
        users.value = res.data || []
        meta.value = res.meta || null
    } catch (err) {
        const status = err?.response?.status
        if (status === 403) {
            loadError.value = 'Foydalanuvchilar ro‘yxatini faqat super admin ko‘ra oladi.'
        } else if (status === 401) {
            loadError.value = 'Sessiya tugagan. Qayta kiring.'
        } else {
            loadError.value = err?.response?.data?.message || 'Ma’lumotlarni yuklashda xatolik.'
        }
        users.value = []
        meta.value = null
    } finally {
        loading.value = false
    }
}

const removeUser = async (id) => {
    if (!window.confirm('Foydalanuvchini o‘chirishni tasdiqlaysizmi?')) {
        return
    }

    try {
        await userService.remove(id)
        await fetchUsers(meta.value?.current_page || 1)
    } catch (err) {
        const data = err?.response?.data
        alert(data?.message || 'O‘chirishda xatolik yuz berdi')
    }
}

onMounted(() => fetchUsers(1))
</script>
