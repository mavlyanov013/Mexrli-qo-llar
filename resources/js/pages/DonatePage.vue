<template>
    <div class="pt-24 pb-20 min-h-screen bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div v-if="step === 'success'" class="text-center py-20">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
                    <span class="text-4xl text-[#4CAF50]">✓</span>
                </div>

                <h2 class="text-3xl font-bold text-gray-900 mb-3">
                    {{ t('donatePage.successTitle') }}
                </h2>
                <p class="text-gray-600 mb-2">
                    {{ t('donatePage.successText') }}
                </p>
                <p class="text-2xl font-bold text-[#2A7DE1]">
                    {{ Number(finalAmount || 0).toLocaleString() }} so'm
                </p>
            </div>

            <div v-else>
                <div class="text-center mb-10">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-orange-50 flex items-center justify-center">
                        <span class="text-3xl text-[#FF9800]">❤</span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                        {{ t('donatePage.title') }}
                    </h1>
                    <p class="text-gray-500 mt-2">
                        {{ t('donatePage.subtitle') }}
                    </p>

                    <div v-if="selectedServiceLabel" class="mt-4">
                        <div class="inline-flex items-center gap-2 bg-orange-50 text-[#FF9800] px-4 py-2 rounded-full text-sm font-medium">
                            {{ t('donatePage.selectedService') }}: {{ selectedServiceLabel }}
                        </div>
                    </div>

                    <div v-if="caseData" class="mt-4 space-y-2">
                        <div class="inline-flex items-center gap-2 bg-blue-50 text-[#2A7DE1] px-4 py-2 rounded-full text-sm font-medium">
                            {{ t('donatePage.donatingFor', { name: caseData.name }) }}
                        </div>

                        <div class="flex items-center justify-center gap-3 text-sm text-gray-500 flex-wrap">
                            <span class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-[#4CAF50]" />
                                {{ t('donatePage.funded', { percent: fundedPercentage }) }}
                            </span>
                            <span>•</span>
                            <span>
                                {{ t('donatePage.remaining', { amount: `${remainingAmount.toLocaleString()} so'm` }) }}
                            </span>
                        </div>
                    </div>
                </div>

                <form class="space-y-6" @submit.prevent="handleSubmit">
                    <div class="bg-white rounded-3xl p-6 md:p-7 border border-gray-100 shadow-sm">
                        <div class="flex gap-2 mb-6">
                            <button
                                type="button"
                                class="flex-1 rounded-2xl px-4 py-3 border font-semibold transition-all"
                                :class="type === 'one_time'
                                    ? 'bg-[#2A7DE1] text-white border-[#2A7DE1]'
                                    : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                @click="type = 'one_time'"
                            >
                                {{ t('donatePage.oneTime') }}
                            </button>

                            <button
                                type="button"
                                class="flex-1 rounded-2xl px-4 py-3 border font-semibold transition-all"
                                :class="type === 'monthly'
                                    ? 'bg-[#2A7DE1] text-white border-[#2A7DE1]'
                                    : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                @click="type = 'monthly'"
                            >
                                {{ t('donatePage.monthly') }}
                            </button>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ t('donatePage.selectedService') }}
                            </label>

                            <select
                                v-model="serviceType"
                                class="rounded-2xl h-12 bg-gray-50 border border-gray-300 w-full px-4 outline-none"
                            >
                                <option value="general">
                                    {{
                                        caseData
                                            ? `${caseData.name} uchun yordam`
                                            : t('donatePage.serviceOptions.general')
                                    }}
                                </option>
                                <option value="education">{{ t('donatePage.serviceOptions.education') }}</option>
                                <option value="surgery">{{ t('donatePage.serviceOptions.surgery') }}</option>
                                <option value="household">{{ t('donatePage.serviceOptions.household') }}</option>
                                <option value="home_repair">{{ t('donatePage.serviceOptions.home_repair') }}</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                            <button
                                v-for="item in presetAmounts"
                                :key="item.amount"
                                type="button"
                                class="rounded-2xl p-4 text-left border-2 transition-all"
                                :class="amount === item.amount && !customAmount
                                    ? 'border-[#2A7DE1] bg-blue-50'
                                    : 'border-gray-200 hover:border-gray-300 bg-white'"
                                @click="selectPreset(item.amount)"
                            >
                                <div class="text-2xl font-bold text-gray-900 leading-none">
                                    {{ Number(item.amount).toLocaleString() }} so'm
                                </div>
                                <div class="text-xs text-gray-500 mt-2 leading-5">
                                    {{ item.label }}
                                </div>
                            </button>
                        </div>

                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium pointer-events-none">
                                so'm
                            </span>
                            <input
                                v-model="customAmount"
                                type="number"
                                min="1000"
                                step="1000"
                                :placeholder="t('donatePage.customAmount')"
                                class="pl-16 rounded-2xl h-12 text-lg bg-gray-50 border border-gray-300 w-full pr-4 outline-none"
                            />
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-6 md:p-7 border border-gray-100 shadow-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ t('donatePage.paymentMethod') }}
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <button
                                type="button"
                                class="rounded-2xl p-4 border-2 text-left transition-all"
                                :class="paymentMethod === 'paycom'
                ? 'border-[#2A7DE1] bg-blue-50'
                : 'border-gray-200 hover:border-gray-300'"
                                @click="paymentMethod = 'paycom'"
                            >
                                <div class="font-bold text-gray-900">Payme</div>
                                <div class="text-sm text-gray-500 mt-1">Online payment</div>
                            </button>

                            <button
                                type="button"
                                class="rounded-2xl p-4 border-2 text-left transition-all"
                                :class="paymentMethod === 'click'
                ? 'border-[#2A7DE1] bg-blue-50'
                : 'border-gray-200 hover:border-gray-300'"
                                @click="paymentMethod = 'click'"
                            >
                                <div class="font-bold text-gray-900">Click</div>
                                <div class="text-sm text-gray-500 mt-1">Online payment</div>
                            </button>

                            <button
                                type="button"
                                class="rounded-2xl p-4 border-2 text-left transition-all"
                                :class="paymentMethod === 'paynet'
                ? 'border-[#2A7DE1] bg-blue-50'
                : 'border-gray-200 hover:border-gray-300'"
                                @click="paymentMethod = 'paynet'"
                            >
                                <div class="font-bold text-gray-900">Paynet</div>
                                <div class="text-sm text-gray-500 mt-1">Paynet orqali to'lov</div>
                            </button>

                            <button
                                type="button"
                                class="rounded-2xl p-4 border-2 text-left transition-all"
                                :class="paymentMethod === 'uzumbank'
                ? 'border-[#2A7DE1] bg-blue-50'
                : 'border-gray-200 hover:border-gray-300'"
                                @click="paymentMethod = 'uzumbank'"
                            >
                                <div class="font-bold text-gray-900">Uzum Bank</div>
                                <div class="text-sm text-gray-500 mt-1">Uzum Bank orqali to'lov</div>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-6 md:p-7 border border-gray-100 shadow-sm space-y-4">
                        <input
                            v-model="name"
                            type="text"
                            :placeholder="t('donatePage.fullName')"
                            class="rounded-2xl h-12 border border-gray-300 w-full px-4 outline-none"
                            required
                        />

                        <input
                            v-model="email"
                            type="email"
                            :placeholder="t('donatePage.email')"
                            class="rounded-2xl h-12 border border-gray-300 w-full px-4 outline-none"
                            required
                        />

                        <textarea
                            v-model="message"
                            rows="4"
                            :placeholder="t('donatePage.messageOptional')"
                            class="rounded-2xl resize-none border border-gray-300 w-full px-4 py-3 outline-none"
                        />

                        <label class="flex items-center gap-2">
                            <input v-model="anonymous" type="checkbox" />
                            <span class="text-sm text-gray-600">
                                {{ t('donatePage.anonymous') }}
                            </span>
                        </label>
                    </div>

                    <p v-if="errorText" class="text-sm text-red-600 text-center">
                        {{ errorText }}
                    </p>

                    <div class="flex items-center gap-2 text-sm text-gray-500 justify-center flex-wrap">
                        <span>🔒 {{ t('donatePage.secure') }}</span>
                        <span>🛡 {{ t('donatePage.goesToCause') }}</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting || !finalAmount"
                        class="w-full h-14 bg-[#FF9800] hover:bg-[#F57C00] text-white rounded-2xl text-lg font-semibold shadow-xl shadow-orange-200/50 disabled:opacity-60"
                    >
                        <span v-if="submitting">{{ t('donatePage.processing') }}</span>
                        <span v-else>
                            ❤ {{ t('donatePage.completeDonation', { amount: `${Number(finalAmount || 0).toLocaleString()} so'm` }) }}
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import donationService from '../services/donationService'

const { t, tm } = useI18n()
const route = useRoute()

const allowedServices = ['education', 'surgery', 'household', 'home_repair', 'general']

const presetAmounts = computed(() => [
    { amount: 10000, label: tm('donatePage.presetLabels')[0] },
    { amount: 25000, label: tm('donatePage.presetLabels')[1] },
    { amount: 50000, label: tm('donatePage.presetLabels')[2] },
    { amount: 100000, label: tm('donatePage.presetLabels')[3] },
    { amount: 250000, label: tm('donatePage.presetLabels')[4] },
    { amount: 500000, label: tm('donatePage.presetLabels')[5] },
])

const caseId = ref(route.query.caseId || '')
const caseData = ref(null)

const initialService = allowedServices.includes(route.query.service)
    ? route.query.service
    : 'general'

const serviceType = ref(initialService)

const step = ref('form')
const type = ref('one_time')
const amount = ref(10000)
const customAmount = ref('')
const name = ref('')
const email = ref('')
const message = ref('')
const anonymous = ref(false)
const paymentMethod = ref('paycom')
const submitting = ref(false)
const errorText = ref('')

const selectedServiceLabel = computed(() => t(`donatePage.serviceOptions.${serviceType.value}`))

const finalAmount = computed(() => {
    const value = customAmount.value ? parseFloat(customAmount.value) : amount.value
    return Number.isFinite(value) ? value : 0
})

const fundedPercentage = computed(() => {
    const raised = Number(caseData.value?.raised_amount || 0)
    const goal = Number(caseData.value?.goal_amount || 1)
    return Math.round((raised / goal) * 100)
})

const remainingAmount = computed(() => {
    return Math.max(
        Number(caseData.value?.goal_amount || 0) - Number(caseData.value?.raised_amount || 0),
        0
    )
})

const selectPreset = (value) => {
    amount.value = value
    customAmount.value = ''
}

const fetchCase = async () => {
    if (!caseId.value) return

    try {
        const response = await api.get(`/cases/${caseId.value}`)
        caseData.value = response?.data?.data || response?.data || null
    } catch {
        caseData.value = null
    }
}

watch(
    () => route.query.service,
    (value) => {
        serviceType.value = allowedServices.includes(value) ? value : 'general'
    },
    { immediate: true }
)

const handleSubmit = async () => {
    errorText.value = ''

    if (!finalAmount.value || Number(finalAmount.value) < 1000) {
        errorText.value = t('donatePage.invalidAmount')
        return
    }

    submitting.value = true

    try {
        const checkout = await donationService.initCheckout({
            case_id: caseId.value || undefined,
            service_type: serviceType.value,
            donor_name: name.value,
            donor_email: email.value,
            amount: Number(finalAmount.value),
            currency: 'UZS',
            type: type.value,
            message: message.value,
            is_anonymous: anonymous.value,
            payment_method: paymentMethod.value,
        })

        if (checkout?.checkout_url) {
            window.location.href = checkout.checkout_url
            return
        }

        errorText.value = t('donatePage.checkoutUnavailable')
    } catch (error) {
        console.error('Checkout init error:', error)
        errorText.value = error?.response?.data?.message || t('donatePage.checkoutError')
    } finally {
        submitting.value = false
    }
}

onMounted(fetchCase)
</script>
