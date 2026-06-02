<template>
    <div class="pt-24 pb-20 min-h-screen bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div v-if="step === 'success'" class="text-center py-20">
                <IconBadge :icon="CircleCheck" tone="green" size="lg" class="mx-auto mb-6" />

                <h2 class="text-3xl font-bold text-gray-900 mb-3">
                    {{ t('donatePage.successTitle') }}
                </h2>
                <p class="text-gray-600 mb-2">
                    {{ t('donatePage.successText') }}
                </p>
                <p class="text-2xl font-bold text-[#2A7DE1]">
                    {{ formatSum(finalAmount) }}
                </p>
            </div>

            <div v-else>
                <div class="text-center mb-10">
                    <IconBadge :icon="Heart" tone="red" size="lg" class="mx-auto mb-4" />

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
                            {{ t('donatePage.donatingFor', { name: content(caseData, 'name') }) }}
                        </div>

                        <div class="flex items-center justify-center gap-3 text-sm text-gray-500 flex-wrap">
                            <span class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-[#4CAF50]" />
                                {{ t('donatePage.funded', { percent: fundedPercentage }) }}
                            </span>
                            <span>•</span>
                            <span>
                                {{ t('donatePage.remaining', { amount: formatSum(remainingAmount) }) }}
                            </span>
                        </div>
                    </div>
                </div>

                <form class="space-y-6" @submit.prevent="handleSubmit">
                    <div class="bg-white rounded-3xl p-6 md:p-7 border border-gray-100 shadow-sm">
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
                                            ? t('public.donate.forCaseSuffix', { name: content(caseData, 'name') })
                                            : t('donatePage.serviceOptions.general')
                                    }}
                                </option>
                                <option value="education">{{ t('donatePage.serviceOptions.education') }}</option>
                                <option value="surgery">{{ t('donatePage.serviceOptions.surgery') }}</option>
                                <option value="household">{{ t('donatePage.serviceOptions.household') }}</option>
                                <option value="home_repair">{{ t('donatePage.serviceOptions.home_repair') }}</option>
                            </select>
                        </div>

                        <div v-if="!caseIdFromRoute" class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ t('donatePage.selectCase') }}
                            </label>

                            <select
                                v-model="caseId"
                                :disabled="casesLoading"
                                class="rounded-2xl h-12 bg-gray-50 border border-gray-300 w-full px-4 outline-none disabled:opacity-60"
                                @change="onCaseSelectChange"
                            >
                                <option value="">
                                    {{ casesLoading ? t('donatePage.casesLoading') : t('donatePage.noCaseSelected') }}
                                </option>
                                <option
                                    v-for="caseItem in activeCases"
                                    :key="caseItem.id"
                                    :value="String(caseItem.id)"
                                >
                                    {{ caseOptionLabel(caseItem) }}
                                </option>
                            </select>

                            <p v-if="!casesLoading && activeCases.length === 0" class="text-sm text-gray-500 mt-2">
                                {{ t('donatePage.noActiveCases') }}
                            </p>
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
                                    {{ formatSum(item.amount) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-2 leading-5">
                                    {{ item.label }}
                                </div>
                            </button>
                        </div>

                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium pointer-events-none">
                                {{ t('public.donate.sumSuffix') }}
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

                    <div class="bg-white rounded-3xl p-6 md:p-7 border border-gray-100 shadow-sm space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ t('donatePage.fullName') }}
                            </label>
                            <input
                                v-model="donorName"
                                type="text"
                                :disabled="isAnonymous"
                                :placeholder="t('donatePage.fullName')"
                                class="rounded-2xl h-12 bg-gray-50 border border-gray-300 w-full px-4 outline-none disabled:opacity-60"
                            />
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input
                                v-model="isAnonymous"
                                type="checkbox"
                                class="rounded border-gray-300 text-[#2A7DE1] focus:ring-[#2A7DE1]"
                            />
                            {{ t('donatePage.anonymous') }}
                        </label>
                    </div>

                    <div class="bg-white rounded-3xl p-6 md:p-7 border border-gray-100 shadow-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ t('donatePage.paymentMethod') }}
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button
                                v-for="option in paymentOptions"
                                :key="option.value"
                                type="button"
                                class="rounded-2xl p-4 text-left border-2 transition-all"
                                :class="paymentMethod === option.value
                                    ? 'border-[#2A7DE1] bg-blue-50'
                                    : 'border-gray-200 hover:border-gray-300 bg-white'"
                                @click="paymentMethod = option.value"
                            >
                                <div class="font-semibold text-gray-900">
                                    {{ option.title }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ option.description }}
                                </div>
                            </button>
                        </div>
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
                        <span v-else class="inline-flex items-center justify-center gap-2">
                            <IconBadge :icon="Heart" tone="red" size="xs" class="shrink-0" />
                            {{ t('donatePage.completeDonation', { amount: formatSum(finalAmount) }) }}
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
import { CircleCheck, Heart } from 'lucide-vue-next'
import IconBadge from '@/components/shared/IconBadge.vue'
import { useLocalizedDisplay } from '@/composables/useLocalizedDisplay'
import api from '../services/api'
import { normalizeList } from '../services/serviceHelpers'
import donationService from '../services/donationService'
import caseService from '../services/caseService'
import { formatAmount, formatMoneyAmount } from '@/utils/formatAmount'
import { PAYMENT_PROVIDERS } from '@/constants/payments'

const { t, tm } = useI18n()
const formatSum = (value) => formatMoneyAmount(value, t('public.donate.sumSuffix'))
const { content } = useLocalizedDisplay()
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

const caseIdFromRoute = ref(route.query.caseId ? String(route.query.caseId) : '')
const caseId = ref(caseIdFromRoute.value)
const caseData = ref(null)
const activeCases = ref([])
const casesLoading = ref(false)

const initialService = allowedServices.includes(route.query.service)
    ? route.query.service
    : 'general'

const serviceType = ref(initialService)

const step = ref('form')
const amount = ref(10000)
const customAmount = ref('')
const paymentMethod = ref(PAYMENT_PROVIDERS.paycom)
const donorName = ref('')
const isAnonymous = ref(false)
const submitting = ref(false)
const errorText = ref('')

const paymentOptions = computed(() => (
    Object.values(PAYMENT_PROVIDERS).map((value) => ({
        value,
        title: t(`public.donate.paymentMethods.${value}.title`),
        description: t(`public.donate.paymentMethods.${value}.description`),
    }))
))

const selectedServiceLabel = computed(() => {
    return t(`donatePage.serviceOptions.${serviceType.value}`)
})

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

const caseOptionLabel = (caseItem) => {
    return content(caseItem, 'name') || caseItem.title || caseItem.name || ''
}

const selectPreset = (value) => {
    amount.value = value
    customAmount.value = ''
}

const syncCaseData = async () => {
    if (!caseId.value) {
        caseData.value = null
        return
    }

    const fromList = activeCases.value.find((item) => String(item.id) === String(caseId.value))
    if (fromList) {
        caseData.value = fromList
        return
    }

    const result = await caseService.getCaseById(caseId.value, { admin: false })
    caseData.value = result.data || null
}

const fetchActiveCases = async () => {
    casesLoading.value = true

    try {
        const response = await api.get('/cases/active')
        activeCases.value = normalizeList(response)
    } catch (error) {
        console.error('Active cases load error:', error)
        activeCases.value = []
    } finally {
        casesLoading.value = false
    }
}

const onCaseSelectChange = async () => {
    await syncCaseData()
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
        const payload = {
            service_type: serviceType.value,
            donor_name: isAnonymous.value
                ? t('common.anonymous')
                : (donorName.value.trim() || t('common.donor')),
            amount: Number(finalAmount.value),
            currency: 'UZS',
            type: 'one_time',
            is_anonymous: isAnonymous.value,
            payment_method: paymentMethod.value,
        }

        if (caseId.value) {
            payload.case_id = caseId.value
        }

        const checkout = await donationService.initCheckout(payload)

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

onMounted(async () => {
    await fetchActiveCases()
    await syncCaseData()
})
</script>
