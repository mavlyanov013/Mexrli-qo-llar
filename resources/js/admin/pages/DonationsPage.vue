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
const { formatMoney } = useAdminUiHelpers()
const { loading, donations } = storeToRefs(store)

onMounted(() => {
    store.loadAll()
})

const filteredDonations = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return donations.value

    return donations.value.filter((donation) =>
        String(donation.donor_name || '').toLowerCase().includes(q) ||
        String(donation.amount || '').toLowerCase().includes(q) ||
        String(donation.type || '').toLowerCase().includes(q)
    )
})
</script>

<template>
    <div class="space-y-4">
        <AdminSearchInput v-model="search" :placeholder="t('admin.search') || 'Qidiruv...'" />

        <div v-if="loading" class="text-gray-500">
            {{ t('admin.loading') }}
        </div>

        <div v-else class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold">{{ t('admin.donations') }} ({{ filteredDonations.length }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4">{{ t('admin.donor') }}</th>
                            <th class="text-left p-4">{{ t('admin.amount') }}</th>
                            <th class="text-left p-4">{{ t('admin.type') }}</th>
                            <th class="text-left p-4">{{ t('admin.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="donation in filteredDonations" :key="donation.id" class="border-t">
                            <td class="p-4">{{ donation.is_anonymous ? t('admin.anonymous') : donation.donor_name }}</td>
                            <td class="p-4">{{ formatMoney(donation.amount) }}</td>
                            <td class="p-4">{{ donation.type || '—' }}</td>
                            <td class="p-4">{{ donation.created_at || donation.created_date || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="filteredDonations.length === 0" class="p-4 text-sm text-gray-500">
                {{ t('admin.noDonations') || 'No donations' }}
            </div>
        </div>
    </div>
</template>
