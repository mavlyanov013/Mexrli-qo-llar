<template>
    <AdminCrudShell :title="title" :subtitle="t('admin.paymentsHint')">

        <template v-if="isListMode">
            <div class="mb-4 space-y-3">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <AdminSearchInput
                        v-model="filters.q"
                        :placeholder="t('admin.placeholders.searchPayments')"
                    />

                    <select
                        v-model="filters.status"
                        class="h-10 rounded-lg border border-gray-300 px-3 text-sm"
                    >
                        <option value="">{{ t('admin.allStatuses') }}</option>
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
                        <option value="">{{ t('admin.allProviders') }}</option>
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
                        <label class="text-xs font-medium text-gray-500">{{ t('admin.dateFrom') }}</label>
                        <input
                            v-model="filters.date_from"
                            type="date"
                            class="h-10 rounded-lg border border-gray-300 px-3 text-sm"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">{{ t('admin.dateTo') }}</label>
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
                            {{ t('admin.filter') }}
                        </button>

                        <button
                            type="button"
                            class="h-10 flex-1 rounded-lg border border-gray-300 px-4 text-sm text-gray-700 hover:border-gray-400"
                            @click="clearFilters"
                        >
                            {{ t('admin.reset') }}
                        </button>

                        <button
                            type="button"
                            class="h-10 flex-1 rounded-lg border border-emerald-300 bg-emerald-50 px-4 text-sm font-medium text-emerald-700 hover:bg-emerald-100 disabled:opacity-60"
                            :disabled="exporting || loading"
                            @click="exportCsv"
                        >
                            {{ exporting ? t('admin.exporting') : t('admin.exportCsv') }}
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
                        {{ formatAmountDisplay(row.amount, row.currency) }}
                    </template>

                    <template #cell-transaction_id="{ row }">
                        {{ row.transaction_id || '—' }}
                    </template>

                    <template #cell-donor_name="{ row }">
                        <span v-if="row.is_anonymous">{{ t('admin.anonymous') }}</span>
                        <span v-else>{{ row.donor_name || row.payload?.donor_name || row.donation?.donor_name || '—' }}</span>
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
                v-if="meta && meta.last_page > 1"
                :current-page="meta.current_page || 1"
                :last-page="meta.last_page || 1"
                :summary="`${meta.total || 0} ta natija`"
                @change="fetchPage"
            />
        </template>

    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
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
import { formatMoneyAmount } from '@/utils/formatAmount'
import paymentService from '@/services/paymentService'
import { downloadExport, fetchAllPaginatedResults } from '@/utils/downloadExport'

const { t } = useI18n()
const route = useRoute()

const { payments, loading, error, fetchPayments, meta } = usePayments()

const isListMode = computed(() => route.name === 'admin-payments')
const title = computed(() => t('admin.payments'))

const columns = computed(() => [
    { key: 'id', label: 'ID' },
    { key: 'transaction_id', label: t('admin.transactionId') },
    { key: 'provider', label: t('admin.provider') },
    { key: 'amount', label: t('admin.amount') },
    { key: 'donor_name', label: t('admin.donorName') },
    { key: 'created_at', label: t('admin.date') },
    { key: 'status', label: t('admin.status') },
])

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
const exporting = ref(false)

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

const formatAmountDisplay = (amount, currency = 'UZS') => {
    return formatMoneyAmount(amount, currency || 'UZS')
}

const resolveDonorName = (row) => {
    if (row.is_anonymous) return t('admin.anonymous')
    return row.donor_name || row.payload?.donor_name || row.donation?.donor_name || ''
}

const resolvePaymentCell = (row, key) => {
    if (key === 'provider') return providerLabel(row.provider)
    if (key === 'amount') return formatAmountDisplay(row.amount, row.currency)
    if (key === 'donor_name') return resolveDonorName(row)
    if (key === 'created_at') return formatDate(row.created_at)
    if (key === 'status') return PAYMENT_STATUSES[row.status]?.label || row.status || ''
    return row[key] ?? ''
}

const buildExportParams = () => {
    const params = { per_page: 200 }

    if (appliedFilters.q) params.q = appliedFilters.q
    if (appliedFilters.provider) params.provider = appliedFilters.provider
    if (appliedFilters.status) params.status = appliedFilters.status
    if (appliedFilters.date_from) params.date_from = appliedFilters.date_from
    if (appliedFilters.date_to) params.date_to = appliedFilters.date_to

    return params
}

const exportCsv = async () => {
    exporting.value = true

    try {
        await downloadExport({
            filename: `online-tolovlar-${new Date().toISOString().slice(0, 10)}.csv`,
            columns: columns.value,
            rows: payments.value,
            getCellValue: resolvePaymentCell,
            fetchAllRows: () => fetchAllPaginatedResults(
                (p) => paymentService.fetchList(p),
                buildExportParams(),
            ),
        })
    } finally {
        exporting.value = false
    }
}

onMounted(() => {
    fetchPage(1)
})
</script>
