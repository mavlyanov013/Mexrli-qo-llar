<script setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useI18n } from 'vue-i18n'
import { useAdminStore } from '@/admin/stores/useAdminStore'
import { useAdminUiHelpers } from '@/admin/composables/useAdminUiHelpers'

const { t } = useI18n()
const store = useAdminStore()
const { formatMoney } = useAdminUiHelpers()
const { loading, totalDonated, paymentCount, activeCasesCount, pendingHelpRequests, newMessages } = storeToRefs(store)

onMounted(() => {
    store.loadAll()
})
</script>

<template>
    <div class="space-y-6">
        <div v-if="loading" class="text-gray-500">
            {{ t('admin.loading') }}
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-sm text-gray-500">{{ t('admin.totalDonations') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatMoney(totalDonated) }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-sm text-gray-500">{{ t('admin.payments') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ paymentCount }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-sm text-gray-500">{{ t('admin.activeCases') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ activeCasesCount }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-sm text-gray-500">{{ t('admin.pendingHelpRequests') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ pendingHelpRequests }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <p class="text-sm text-gray-500">{{ t('admin.newMessages') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ newMessages }}</p>
            </div>
        </div>
    </div>
</template>
