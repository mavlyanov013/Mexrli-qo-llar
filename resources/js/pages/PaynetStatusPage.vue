<template>
    <div class="pt-24 pb-20 min-h-screen bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ title }}
                </h1>

                <p v-if="loading" class="text-gray-500">{{ t('common.loading') }}</p>

                <template v-else-if="payment">
                    <p class="text-gray-600 mb-2">{{ t('payStatus.paymentId') }}: <strong>{{ payment.id }}</strong></p>

                    <p class="text-gray-600 mb-2">{{ t('payStatus.provider') }}: <strong>{{ payment.provider }}</strong></p>

                    <p class="text-gray-600 mb-2">{{ t('payStatus.transactionId') }}: <strong>{{ payment.transaction_id }}</strong></p>

                    <p class="text-gray-600 mb-2">{{ t('payStatus.amount') }}: <strong>{{ Number(payment.amount).toLocaleString() }} {{ payment.currency }}</strong></p>

                    <div class="mt-4">
                        <StatusBadge :status="payment.status" :map="PAYMENT_STATUSES" />
                    </div>

                    <div class="mt-6 space-y-3">
                        <ExternalLink
                            v-if="providerCheckoutUrl"
                            :href="providerCheckoutUrl"
                            classes="inline-block rounded-2xl px-5 py-3 bg-green-600 text-white font-semibold"
                        >
                            {{ t('payStatus.payNow') }}
                        </ExternalLink>

                        <div>
                            <button
                                type="button"
                                class="rounded-2xl px-5 py-3 bg-[#2A7DE1] text-white font-semibold"
                                @click="fetchStatus"
                            >
                                {{ t('common.refresh') }}
                            </button>
                        </div>
                    </div>
                </template>

                <p v-else class="text-red-600">{{ t('payStatus.notFound') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import paymentService from '../services/paymentService'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import ExternalLink from '@/components/shared/ExternalLink.vue'
import { PAYMENT_STATUSES } from '@/constants/statuses'
import { PAYMENT_PROVIDERS } from '@/constants/payments'

const route = useRoute()
const { t } = useI18n()

const payment = ref(null)
const loading = ref(false)
const providers = ref([])

const paymentId = computed(() => route.query.payment_id)
const provider = computed(() => route.query.provider || PAYMENT_PROVIDERS.paynet)
const providerCheckoutUrl = computed(() => {
    const found = providers.value.find((item) => item?.code === provider.value)
    return found?.checkout_url || null
})

const title = computed(() => {
    return provider.value === PAYMENT_PROVIDERS.uzumbank
        ? t('payStatus.uzumTitle')
        : t('payStatus.paynetTitle')
})

const fetchStatus = async () => {
    if (!paymentId.value) return

    loading.value = true

    try {
        const result = await paymentService.fetchPublicStatus(provider.value, paymentId.value)
        payment.value = result.data
    } catch (e) {
        payment.value = null
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    const providerResult = await paymentService.fetchProviderConfigs()
    providers.value = providerResult.data || []
    await fetchStatus()
})
</script>
