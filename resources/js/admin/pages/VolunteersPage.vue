<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { useAdminStore } from '@/admin/stores/useAdminStore'
import { useAdminUiHelpers } from '@/admin/composables/useAdminUiHelpers'
import AdminSearchInput from '@/admin/components/common/AdminSearchInput.vue'

const { t } = useI18n()
const search = ref('')
const store = useAdminStore()
const { badgeClass } = useAdminUiHelpers()
const { loading, volunteers } = storeToRefs(store)

onMounted(() => {
    store.loadAll()
})

const filteredVolunteers = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return volunteers.value

    return volunteers.value.filter((volunteer) =>
        String(volunteer.full_name || '').toLowerCase().includes(q) ||
        String(volunteer.email || '').toLowerCase().includes(q) ||
        String(volunteer.role_interest || '').toLowerCase().includes(q) ||
        String(volunteer.status || '').toLowerCase().includes(q)
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
                <h2 class="text-xl font-bold">{{ t('admin.volunteers') }} ({{ filteredVolunteers.length }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.fullName') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.email') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.role') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.availability') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.status') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.city') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="volunteer in filteredVolunteers" :key="volunteer.id" class="border-t">
                            <td class="p-4">{{ volunteer.full_name }}</td>
                            <td class="p-4">{{ volunteer.email }}</td>
                            <td class="p-4">{{ t(`adminVolunteer.roles.${volunteer.role_interest || 'other'}`) }}</td>
                            <td class="p-4">{{ t(`adminVolunteer.availability.${volunteer.availability || 'flexible'}`) }}</td>
                            <td class="p-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(volunteer.status)">
                                    {{ t(`adminVolunteer.statuses.${volunteer.status || 'pending'}`) }}
                                </span>
                            </td>
                            <td class="p-4">{{ volunteer.city || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="filteredVolunteers.length === 0" class="p-4 text-sm text-gray-500">
                {{ t('admin.noVolunteers') }}
            </div>
        </div>
    </div>
</template>
