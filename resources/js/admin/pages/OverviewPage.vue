<script setup>
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { useAdminStore } from '@/admin/stores/useAdminStore'
import { useAdminUiHelpers } from '@/admin/composables/useAdminUiHelpers'

const { t } = useI18n()
const store = useAdminStore()
const { formatMoney, badgeClass } = useAdminUiHelpers()
const { loading, totalDonated, activeCasesCount, pendingHelpRequests, newMessages, paymentCount, payments, volunteers } =
    storeToRefs(store)

onMounted(() => {
    store.loadAll()
})
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-2xl font-bold text-gray-900">{{ formatMoney(totalDonated) }}</p>
                <p class="text-sm text-gray-500">{{ t('admin.totalDonations') }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-2xl font-bold text-gray-900">{{ activeCasesCount }}</p>
                <p class="text-sm text-gray-500">{{ t('admin.activeCases') }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-2xl font-bold text-gray-900">{{ pendingHelpRequests }}</p>
                <p class="text-sm text-gray-500">{{ t('admin.pendingHelpRequests') }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-2xl font-bold text-gray-900">{{ newMessages }}</p>
                <p class="text-sm text-gray-500">{{ t('admin.newMessages') }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-2xl font-bold text-gray-900">{{ paymentCount }}</p>
                <p class="text-sm text-gray-500">{{ t('admin.payments') }}</p>
            </div>
        </div>

        <div v-if="loading" class="text-gray-500">
            {{ t('admin.loading') }}
        </div>

        <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-bold text-gray-900">{{ t('admin.payments') }}</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="payment in payments.slice(0, 5)"
                        :key="payment.id"
                        class="p-4 flex items-center justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 uppercase">{{ payment.provider }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ payment.transaction_id }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-semibold text-gray-900">{{ formatMoney(payment.amount) }}</p>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(payment.status)">
                                {{ payment.status }}
                            </span>
                        </div>
                    </div>

                    <div v-if="payments.length === 0" class="p-4 text-sm text-gray-500">
                        {{ t('admin.noPayments') }}
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-bold text-gray-900">{{ t('admin.volunteers') }}</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="volunteer in volunteers.slice(0, 5)"
                        :key="volunteer.id"
                        class="p-4 flex items-center justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900">{{ volunteer.full_name }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ volunteer.email }}</p>
                        </div>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium shrink-0" :class="badgeClass(volunteer.status)">
                            {{ t(`adminVolunteer.statuses.${volunteer.status || 'pending'}`) }}
                        </span>
                    </div>

                    <div v-if="volunteers.length === 0" class="p-4 text-sm text-gray-500">
                        {{ t('admin.noVolunteers') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
