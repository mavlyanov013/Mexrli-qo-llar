<template>
    <AdminCrudShell :title="title">

        <template v-if="isListMode">
            <div class="mb-4 space-y-3">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <AdminSearchInput
                        v-model="filters.q"
                        placeholder="Qidirish (ID, tranzaksiya, donor)..."
                    />

                    <select
                        v-model="filters.status"
                        class="h-10 rounded-lg border border-gray-300 px-3 text-sm"
                    >
                        <option value="">Barcha holatlar</option>
                        <option
                            v-for="status in PAYMENT_STATUS_FILTER_OPTIONS"
                            :key="status.value"
                            :value="status.value"
                        >
                            {{ status.label }}
                        </option>
                    </select>

                    <select
                        v-model="filters.provider"
                        class="h-10 rounded-lg border border-gray-300 px-3 text-sm"
                    >
                        <option value="">Barcha to‘lov tizimlari</option>
                        <option
                            v-for="provider in ONLINE_PAYMENT_PROVIDER_OPTIONS"
                            :key="provider.value"
                            :value="provider.value"
                        >
                            {{ provider.label }}
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Sanadan</label>
                        <input
                            v-model="filters.date_from"
                            type="date"
                            class="h-10 rounded-lg border border-gray-300 px-3 text-sm"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Sanagacha</label>
                        <input
                            v-model="filters.date_to"
                            type="date"
                            class="h-10 rounded-lg border border-gray-300 px-3 text-sm"
                        />
                    </div>

                    <div class="flex items-end gap-2 md:col-span-2">
                        <button
                            type="button"
                            class="h-10 flex-1 rounded-lg bg-[#2A7DE1] px-4 text-sm font-medium text-white hover:bg-[#2569c7]"
                            @click="applyFilters"
                        >
                            Filtrlash
                        </button>

                        <button
                            type="button"
                            class="h-10 flex-1 rounded-lg border border-gray-300 px-4 text-sm text-gray-700 hover:border-gray-400"
                            @click="clearFilters"
                        >
                            Tozalash
                        </button>
                    </div>
                </div>
            </div>

            <ListState :loading="loading" :error="error" :empty="payments.length === 0">

                <AdminTable :columns="columns" :rows="payments">

                    <template #cell-provider="{ row }">
                        {{ providerLabel(row.provider) }}
                    </template>

                    <template #cell-amount="{ row }">
                        {{ formatAmount(row.amount, row.currency) }}
                    </template>

                    <template #cell-donor_name="{ row }">
                        {{ row.payload?.donor_name || row.donation?.donor_name || '—' }}
                    </template>

                    <template #cell-donor_phone="{ row }">
                        {{ row.payload?.donor_phone || row.donation?.donor_phone || '—' }}
                    </template>

                    <template #cell-created_at="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>

                    <template #cell-status="{ row }">
                        <StatusBadge :status="row.status" :map="PAYMENT_STATUSES" />
                    </template>

                </AdminTable>

            </ListState>

            <AdminPagination
                v-if="meta"
                :current-page="meta.current_page || 1"
                :last-page="meta.last_page || 1"
                :summary="`${meta.total || 0} ta natija`"
                @change="fetchPage"
            />
        </template>

    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import { PAYMENT_STATUSES } from '@/constants/statuses'
import {
    ONLINE_PAYMENT_PROVIDER_OPTIONS,
    PAYMENT_STATUS_FILTER_OPTIONS,
    providerLabel,
} from '@/constants/payments'
import { usePayments } from '@/composables/usePayments'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import AdminSearchInput from '@/admin/components/common/AdminSearchInput.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const { payments, loading, error, fetchPayments, meta } = usePayments()

const isListMode = computed(() => route.name === 'admin-payments')
const title = computed(() => t('admin.payments'))

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'provider', label: 'To‘lov tizimi' },
    { key: 'amount', label: 'Summa' },
    { key: 'donor_name', label: 'Donor ismi' },
    { key: 'donor_phone', label: 'Telefon' },
    { key: 'created_at', label: 'Sana' },
    { key: 'status', label: 'Holat' },
]

const filters = reactive({
    q: '',
    provider: '',
    status: '',
    date_from: '',
    date_to: '',
})

const appliedFilters = reactive({
    q: '',
    provider: '',
    status: '',
    date_from: '',
    date_to: '',
})

const currentPage = ref(1)

const buildParams = (page) => {
    const params = { page, per_page: 20 }

    if (appliedFilters.q) params.q = appliedFilters.q
    if (appliedFilters.provider) params.provider = appliedFilters.provider
    if (appliedFilters.status) params.status = appliedFilters.status
    if (appliedFilters.date_from) params.date_from = appliedFilters.date_from
    if (appliedFilters.date_to) params.date_to = appliedFilters.date_to

    return params
}

const fetchPage = async (page = 1) => {
    currentPage.value = page
    await fetchPayments(buildParams(page))
}

const applyFilters = async () => {
    appliedFilters.q = filters.q.trim()
    appliedFilters.provider = filters.provider
    appliedFilters.status = filters.status
    appliedFilters.date_from = filters.date_from
    appliedFilters.date_to = filters.date_to
    await fetchPage(1)
}

const clearFilters = async () => {
    filters.q = ''
    filters.provider = ''
    filters.status = ''
    filters.date_from = ''
    filters.date_to = ''
    appliedFilters.q = ''
    appliedFilters.provider = ''
    appliedFilters.status = ''
    appliedFilters.date_from = ''
    appliedFilters.date_to = ''
    await fetchPage(1)
}

const formatDate = (value) => {
    if (!value) return '—'
    const date = new Date(value)
    return date.toLocaleString('uz-UZ', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const formatAmount = (amount, currency = 'UZS') => {
    const value = Number(amount || 0)
    return `${value.toLocaleString('uz-UZ')} ${currency || 'UZS'}`
}

watch(() => route.fullPath, async () => {
    if (!isListMode.value) {
        router.replace('/admin/payments')
        return
    }

    await fetchPage(currentPage.value)
}, { immediate: true })

onMounted(async () => {
    if (!isListMode.value) {
        router.replace('/admin/payments')
        return
    }

    await fetchPage(1)
})
</script>
