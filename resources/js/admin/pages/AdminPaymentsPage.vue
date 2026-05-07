<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/payments/create' : ''">

        <!-- LIST -->
        <template v-if="isListMode">
            <ListState :loading="loading" :error="error" :empty="payments.length === 0">

                <AdminTable :columns="columns" :rows="payments">

                    <template #cell-status="{ row }">
                        <StatusBadge :status="row.status" :map="PAYMENT_STATUSES" />
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-3">

                            <!-- VIEW -->
                            <router-link
                                :to="`/admin/payments/${row.id}`"
                                class="text-blue-600 hover:scale-110 transition"
                            >
                                <Eye class="w-4 h-4" />
                            </router-link>

                            <!-- EDIT -->
                            <router-link
                                :to="`/admin/payments/${row.id}/edit`"
                                class="text-amber-600 hover:scale-110 transition"
                            >
                                <Pencil class="w-4 h-4" />
                            </router-link>

                            <!-- DELETE -->
                            <button
                                @click="remove(row.id)"
                                class="text-red-600 hover:scale-110 transition"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>

                        </div>
                    </template>

                </AdminTable>

            </ListState>
        </template>

        <!-- VIEW -->
        <template v-else-if="isViewMode && current">
            <div class="space-y-2 text-sm">
                <p><strong>ID:</strong> {{ current.id }}</p>
                <p><strong>{{ t('admin.provider') }}:</strong> {{ current.provider }}</p>
                <p><strong>{{ t('admin.transactionId') }}:</strong> {{ current.transaction_id }}</p>
                <p><strong>{{ t('admin.amount') }}:</strong> {{ current.amount }} {{ current.currency }}</p>
                <p>
                    <strong>{{ t('admin.status') }}:</strong>
                    <StatusBadge :status="current.status" :map="PAYMENT_STATUSES" />
                </p>
            </div>
        </template>

        <!-- CREATE / EDIT -->
        <template v-else>
            <form class="grid grid-cols-1 gap-3 md:grid-cols-2" @submit.prevent="save">

                <!-- ONLY CASH (NAQT) -->
                <select v-model="form.provider" class="h-10 rounded-lg border px-3 text-sm">
                    <option value="cash">cash</option>
                </select>

                <input v-model="form.transaction_id" class="h-10 rounded-lg border px-3 text-sm"
                       :placeholder="t('admin.transactionId')" required />

                <input v-model.number="form.amount" type="number" min="0" step="0.01"
                       class="h-10 rounded-lg border px-3 text-sm"
                       :placeholder="t('admin.amount')" required />

                <input v-model="form.currency" class="h-10 rounded-lg border px-3 text-sm" placeholder="UZS" />

<!--                <select v-model="form.status" class="h-10 rounded-lg border px-3 text-sm">-->
<!--                    <option v-for="status in PAYMENT_STATUS_OPTIONS" :key="status" :value="status">-->
<!--                        {{ status }}-->
<!--                    </option>-->
<!--                </select>-->

                <div class="md:col-span-2 flex gap-2">
                    <button class="rounded-lg bg-blue-600 px-4 py-2 text-white">
                        {{ t('admin.save') }}
                    </button>

                    <router-link to="/admin/payments"
                                 class="rounded-lg border px-4 py-2">
                        {{ t('admin.cancel') }}
                    </router-link>
                </div>

            </form>
        </template>

    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import { PAYMENT_STATUS_OPTIONS, PAYMENT_STATUSES } from '@/constants/statuses'
import { usePayments } from '@/composables/usePayments'
import paymentService from '@/services/paymentService'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import {Eye, Pencil, Trash2} from "lucide-vue-next";

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const {
    payments,
    loading,
    error,
    fetchPayments,
    createPayment,
    updatePayment,
    deletePayment
} = usePayments()

const current = ref(null)

const isListMode = computed(() => route.name === 'admin-payments')
const isEditMode = computed(() => route.name === 'admin-payments-edit')
const isViewMode = computed(() => route.name === 'admin-payments-view')

const title = computed(() =>
    isListMode.value
        ? t('admin.payments')
        : isEditMode.value
            ? 'Edit payment'
            : isViewMode.value
                ? 'Payment details'
                : 'Create payment'
)

const form = reactive({
    provider: 'cash',
    transaction_id: '',
    amount: 0,
    status: 'pending',
    currency: 'UZS',
})

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'provider', label: t('admin.provider') },
    { key: 'transaction_id', label: t('admin.transactionId') },
    { key: 'status', label: t('admin.status') },
    { key: 'actions', label: t('admin.actions') },
]

const loadCurrent = async () => {
    if (!route.params.id) return

    const res = await paymentService.getById(route.params.id)
    current.value = res.data

    if (isEditMode.value && res.data) {
        Object.assign(form, {
            provider: res.data.provider || 'cash',
            transaction_id: res.data.transaction_id || '',
            amount: Number(res.data.amount || 0),
            status: res.data.status || 'pending',
            currency: res.data.currency || 'UZS',
        })
    }
}

const save = async () => {
    const payload = { ...form }

    const result = isEditMode.value
        ? await updatePayment(route.params.id, payload)
        : await createPayment(payload)

    if (!result.error) {
        router.push('/admin/payments')
    }
}

const remove = async (id) => {
    if (!confirm('Delete this payment?')) return
    await deletePayment(id)
    await fetchPayments()
}

/* FIX: route change bug (view/edit ishlamay qolishi shu sabab bo‘ladi) */
watch(() => route.fullPath, async () => {
    if (isListMode.value) {
        await fetchPayments()
    } else {
        await loadCurrent()
    }
}, { immediate: true })

onMounted(async () => {
    if (isListMode.value) await fetchPayments()
    else await loadCurrent()
})
</script>
