<script setup>
import { computed, onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useI18n } from 'vue-i18n'
import { useAdminStore } from '@/admin/stores/useAdminStore'
import { useAdminUiHelpers } from '@/admin/composables/useAdminUiHelpers'
import AdminSearchInput from '@/admin/components/common/AdminSearchInput.vue'

const { t } = useI18n()
const search = ref('')
const store = useAdminStore()
const { badgeClass } = useAdminUiHelpers()
const { loading, users } = storeToRefs(store)

onMounted(() => {
    store.loadAll()
})

const filteredUsers = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return users.value

    return users.value.filter((user) =>
        String(user.name || '').toLowerCase().includes(q) ||
        String(user.email || '').toLowerCase().includes(q) ||
        String(user.status || '').toLowerCase().includes(q)
    )
})
</script>

<template>
    <div class="space-y-4">
        <AdminSearchInput v-model="search" :placeholder="t('admin.searchPlaceholder')" />

        <div v-if="loading" class="text-gray-500">
            {{ t('admin.loading') }}
        </div>

        <div v-else class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold">Users ({{ filteredUsers.length }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4">{{ t('admin.name') }}</th>
                            <th class="text-left p-4">Email</th>
                            <th class="text-left p-4">{{ t('admin.status') }}</th>
                            <th class="text-left p-4">Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in filteredUsers" :key="user.id" class="border-t">
                            <td class="p-4">{{ user.name }}</td>
                            <td class="p-4">{{ user.email }}</td>
                            <td class="p-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(user.status)">
                                    {{ user.status }}
                                </span>
                            </td>
                            <td class="p-4">{{ user.source }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="filteredUsers.length === 0" class="p-4 text-sm text-gray-500">
                No users available
            </div>
        </div>
    </div>
</template>
