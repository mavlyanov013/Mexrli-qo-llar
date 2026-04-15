<template>
    <div class="pt-24 pb-20 min-h-screen bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ title }}
                </h1>

                <p v-if="loading" class="text-gray-500">Yuklanmoqda...</p>

                <template v-else-if="payment">
                    <p class="text-gray-600 mb-2">
                        To'lov ID: <strong>{{ payment.id }}</strong>
                    </p>

                    <p class="text-gray-600 mb-2">
                        Provider: <strong>{{ payment.provider }}</strong>
                    </p>

                    <p class="text-gray-600 mb-2">
                        Transaction ID: <strong>{{ payment.transaction_id }}</strong>
                    </p>

                    <p class="text-gray-600 mb-2">
                        Summa: <strong>{{ Number(payment.amount).toLocaleString() }} {{ payment.currency }}</strong>
                    </p>

                    <p class="text-lg font-semibold mt-4" :class="statusClass">
                        {{ statusLabel }}
                    </p>

                    <div class="mt-6 space-y-3">
                        <a
                            v-if="provider === 'paynet'"
                            href="https://app.paynet.uz/?m=4590"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-block rounded-2xl px-5 py-3 bg-green-600 text-white font-semibold"
                        >
                            Paynet orqali to'lash
                        </a>

                        <a
                            v-if="provider === 'uzumbank'"
                            href="https://www.apelsin.uz/open-service?serviceId=12030307"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-block rounded-2xl px-5 py-3 bg-orange-500 text-white font-semibold"
                        >
                            Uzum Bank orqali to'lash
                        </a>

                        <div>
                            <button
                                type="button"
                                class="rounded-2xl px-5 py-3 bg-[#2A7DE1] text-white font-semibold"
                                @click="fetchStatus"
                            >
                                Yangilash
                            </button>
                        </div>
                    </div>
                </template>

                <p v-else class="text-red-600">To'lov topilmadi.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import paymentService from '../services/paymentService'

const route = useRoute()

const payment = ref(null)
const loading = ref(false)

const paymentId = computed(() => route.query.payment_id)
const provider = computed(() => route.query.provider || 'paynet')

const title = computed(() => {
    return provider.value === 'uzumbank'
        ? "Uzum Bank to'lov holati"
        : "Paynet to'lov holati"
})

const statusLabel = computed(() => {
    if (!payment.value) return ''

    return {
        pending: 'Kutilmoqda',
        success: "Muvaffaqiyatli to'landi",
        completed: "Muvaffaqiyatli to'landi",
        cancelled: 'Bekor qilingan',
        failed: 'Xatolik',
        funded: 'Ishlatilgan',
    }[payment.value.status] || payment.value.status
})

const statusClass = computed(() => {
    if (!payment.value) return 'text-gray-700'

    return {
        pending: 'text-yellow-600',
        success: 'text-green-600',
        completed: 'text-green-600',
        cancelled: 'text-red-600',
        failed: 'text-red-600',
        funded: 'text-blue-600',
    }[payment.value.status] || 'text-gray-700'
})

const fetchStatus = async () => {
    if (!paymentId.value) return

    loading.value = true

    try {
        if (provider.value === 'uzumbank') {
            payment.value = await paymentService.getUzumBankStatus(paymentId.value)
        } else {
            payment.value = await paymentService.getPaynetStatus(paymentId.value)
        }
    } catch (e) {
        payment.value = null
    } finally {
        loading.value = false
    }
}

onMounted(fetchStatus)
</script>
