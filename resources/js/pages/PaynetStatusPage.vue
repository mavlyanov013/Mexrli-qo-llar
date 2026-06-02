<template>
    <div class="pt-24 pb-20 min-h-screen bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm text-center">
                <IconBadge
                    v-if="statusTone"
                    :icon="statusIcon"
                    :tone="statusTone"
                    size="lg"
                    class="mx-auto mb-5"
                />

                <h1 class="text-3xl font-bold text-gray-900 mb-3">
                    {{ pageTitle }}
                </h1>

                <p v-if="loading" class="text-gray-500">{{ t('common.loading') }}</p>

                <template v-else-if="payment">
                    <p class="text-gray-600 mb-2">
                        {{ t('payStatus.paymentId') }}: <strong>{{ payment.id }}</strong>
                    </p>

                    <p class="text-gray-600 mb-2">
                        {{ t('payStatus.provider') }}: <strong>{{ payment.provider }}</strong>
                    </p>

                    <p class="text-gray-600 mb-2">
                        {{ t('payStatus.transactionId') }}: <strong>{{ payment.transaction_id }}</strong>
                    </p>

                    <p class="text-gray-600 mb-4">
                        {{ t('payStatus.amount') }}: <strong>{{ formatPaymentAmount(payment) }}</strong>
                    </p>

                    <div class="flex justify-center mb-4">
                        <StatusBadge :status="payment.status" :map="PAYMENT_STATUSES" />
                    </div>

                    <p v-if="statusMessage" class="text-sm text-gray-600 mb-6">
                        {{ statusMessage }}
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <RouterLink
                            v-if="isSuccess"
                            to="/"
                            class="inline-flex rounded-2xl px-5 py-3 bg-[#2A7DE1] text-white font-semibold"
                        >
                            {{ t('payStatus.backHome') }}
                        </RouterLink>

                        <RouterLink
                            v-if="isCancelled"
                            to="/donate"
                            class="inline-flex rounded-2xl px-5 py-3 bg-[#FF9800] text-white font-semibold"
                        >
                            {{ t('payStatus.tryAgain') }}
                        </RouterLink>

                        <ExternalLink
                            v-if="providerCheckoutUrl && isPending"
                            :href="providerCheckoutUrl"
                            classes="inline-flex rounded-2xl px-5 py-3 bg-green-600 text-white font-semibold"
                        >
                            {{ t('payStatus.payNow') }}
                        </ExternalLink>

                        <button
                            v-if="isPending"
                            type="button"
                            class="rounded-2xl px-5 py-3 border border-gray-300 text-gray-700 font-semibold"
                            @click="fetchStatus"
                        >
                            {{ t('common.refresh') }}
                        </button>
                    </div>
                </template>

                <p v-else class="text-red-600">{{ t('payStatus.notFound') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { CircleCheck, CircleX, Clock3 } from 'lucide-vue-next'
import paymentService from '../services/paymentService'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import ExternalLink from '@/components/shared/ExternalLink.vue'
import IconBadge from '@/components/shared/IconBadge.vue'
import { PAYMENT_STATUSES } from '@/constants/statuses'
import { PAYMENT_PROVIDERS } from '@/constants/payments'
import { formatMoneyAmount } from '@/utils/formatAmount'

const route = useRoute()
const { t } = useI18n()

const formatPaymentAmount = (item) => formatMoneyAmount(item?.amount, item?.currency || 'UZS')

const payment = ref(null)
const loading = ref(false)
const providers = ref([])

const paymentId = computed(() => route.query.payment_id)
const provider = computed(() => route.query.provider || PAYMENT_PROVIDERS.paynet)

const providerCheckoutUrl = computed(() => {
    const found = providers.value.find((item) => item?.code === provider.value)
    return found?.checkout_url || null
})

const normalizedStatus = computed(() => String(payment.value?.status || '').toLowerCase())

const isSuccess = computed(() => ['success', 'completed', 'funded'].includes(normalizedStatus.value))
const isCancelled = computed(() => ['cancelled', 'failed', 'canceled'].includes(normalizedStatus.value))
const isPending = computed(() => !isSuccess.value && !isCancelled.value)

const pageTitle = computed(() => {
    if (loading.value) {
        return provider.value === PAYMENT_PROVIDERS.uzumbank
            ? t('payStatus.uzumTitle')
            : t('payStatus.paynetTitle')
    }

    if (isSuccess.value) return t('payStatus.successTitle')
    if (isCancelled.value) return t('payStatus.cancelTitle')

    return provider.value === PAYMENT_PROVIDERS.uzumbank
        ? t('payStatus.uzumTitle')
        : t('payStatus.paynetTitle')
})

const statusMessage = computed(() => {
    if (isSuccess.value) return t('payStatus.successText')
    if (isCancelled.value) return t('payStatus.cancelText')
    if (isPending.value) return t('payStatus.pendingText')
    return ''
})

const statusTone = computed(() => {
    if (loading.value || !payment.value) return null
    if (isSuccess.value) return 'green'
    if (isCancelled.value) return 'red'
    return 'blue'
})

const statusIcon = computed(() => {
    if (isSuccess.value) return CircleCheck
    if (isCancelled.value) return CircleX
    return Clock3
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
