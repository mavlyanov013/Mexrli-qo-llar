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
const { formatMoney, badgeClass } = useAdminUiHelpers()
const { loading, payments } = storeToRefs(store)

onMounted(() => {
    store.loadAll()
})

const filteredPayments = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return payments.value

    return payments.value.filter((payment) =>
        String(payment.provider || '').toLowerCase().includes(q) ||
        String(payment.transaction_id || '').toLowerCase().includes(q) ||
        String(payment.payer_reference || '').toLowerCase().includes(q) ||
        String(payment.status || '').toLowerCase().includes(q)
    )
})
</script>

<template>
    <div class="space-y-4">
        <AdminSearchInput v-model="search" :placeholder="t('admin.search') || 'Search...'" />

        <div v-if="loading" class="text-gray-500">
            {{ t('admin.loading') }}
        </div>

        <div v-else class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold">{{ t('admin.payments') }} ({{ filteredPayments.length }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4">{{ t('admin.provider') }}</th>
                            <th class="text-left p-4">{{ t('admin.transactionId') }}</th>
                            <th class="text-left p-4">{{ t('admin.payer') }}</th>
                            <th class="text-left p-4">{{ t('admin.amount') }}</th>
                            <th class="text-left p-4">{{ t('admin.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in filteredPayments" :key="payment.id" class="border-t">
                            <td class="p-4 uppercase font-medium">{{ payment.provider }}</td>
                            <td class="p-4">{{ payment.transaction_id }}</td>
                            <td class="p-4">{{ payment.payer_reference || '—' }}</td>
                            <td class="p-4">{{ formatMoney(payment.amount) }}</td>
                            <td class="p-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(payment.status)">
                                    {{ payment.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="filteredPayments.length === 0" class="p-4 text-sm text-gray-500">
                {{ t('admin.noPayments') }}
            </div>
        </div>
    </div>
</template>
