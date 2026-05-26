<template>
    <div class="pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ t('contactPage.title') }}
                </h1>
                <p class="text-lg text-gray-500">
                    {{ t('contactPage.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        v-for="(item, index) in contactInfo"
                        :key="index"
                        class="flex items-start gap-4"
                    >
                        <IconBadge
                            :icon="item.icon"
                            :tone="item.tone"
                            size="md"
                            class="shrink-0"
                        />
                        <div>
                            <p class="text-sm text-gray-500">{{ item.label }}</p>
                            <p class="font-medium text-gray-900 whitespace-pre-wrap">{{ item.value }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl overflow-hidden h-64 mt-6 bg-gray-200">
                        <iframe
                            v-if="mapUrl"
                            :src="mapUrl"
                            width="100%"
                            height="100%"
                            style="border: 0"
                            :title="t('contactPage.officeLocation')"
                        />
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div
                        v-if="submitted"
                        class="text-center py-16 bg-white rounded-2xl border border-gray-100"
                    >
                        <IconBadge :icon="CircleCheck" tone="green" size="lg" class="mx-auto mb-4" />
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            {{ t('contactPage.successTitle') }}
                        </h2>
                        <p class="text-gray-500">
                            {{ t('contactPage.successText') }}
                        </p>
                    </div>

                    <div v-else class="bg-white rounded-2xl p-8 border border-gray-100">
                        <form class="space-y-4" @submit.prevent="handleSubmit">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <input
                                    v-model="form.full_name"
                                    type="text"
                                    :placeholder="t('contactPage.fullName')"
                                    class="rounded-xl h-11 border border-gray-300 px-4 w-full outline-none"
                                    required
                                />
                                <input
                                    v-model="form.email"
                                    type="email"
                                    :placeholder="t('contactPage.email')"
                                    class="rounded-xl h-11 border border-gray-300 px-4 w-full outline-none"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <PhoneInput
                                    ref="phoneInputRef"
                                    v-model="form.phone"
                                    input-class="rounded-xl h-11 border border-gray-300 px-4 w-full outline-none"
                                />
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    :placeholder="t('contactPage.subject')"
                                    class="rounded-xl h-11 border border-gray-300 px-4 w-full outline-none"
                                />
                            </div>

                            <textarea
                                v-model="form.message"
                                rows="6"
                                :placeholder="t('contactPage.yourMessage')"
                                class="rounded-xl resize-none border border-gray-300 px-4 py-3 w-full outline-none"
                                required
                            />

                            <p v-if="errorText" class="text-sm text-red-600">
                                {{ errorText }}
                            </p>

                            <button
                                type="submit"
                                :disabled="submitting"
                                class="w-full h-12 bg-[#2A7DE1] hover:bg-[#1E6BC9] text-white rounded-xl font-semibold disabled:opacity-60"
                            >
                                {{ submitting ? t('contactPage.sending') : t('contactPage.sendMessage') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import contactService from '../services/contactService'
import contactInfoService from '../services/contactInfoService'
import PhoneInput from '../components/shared/PhoneInput.vue'
import { formatUzbekPhoneDisplay } from '@/utils/uzbekPhone'
import {
    CircleCheck,
    MapPin,
    Phone,
    Mail,
    Clock,
} from 'lucide-vue-next'
import IconBadge from '../components/shared/IconBadge.vue'

const { t } = useI18n()

const submitted = ref(false)
const submitting = ref(false)
const errorText = ref('')
const phoneInputRef = ref(null)
const info = ref(null)

const form = reactive({
    full_name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
})

const displayPhone = (phone) => {
    if (!phone) return '—'
    return formatUzbekPhoneDisplay(phone) || phone
}

const mapUrl = computed(() => info.value?.map_url || '')

const contactInfo = computed(() => {
    const data = info.value || {}

    return [
        {
            icon: MapPin,
            label: t('contactPage.address'),
            value: data.address || '—',
            tone: 'blue',
        },
        {
            icon: Phone,
            label: t('contactPage.phone'),
            value: displayPhone(data.phone),
            tone: 'green',
        },
        {
            icon: Mail,
            label: t('contactPage.email'),
            value: data.email || '—',
            tone: 'orange',
        },
        {
            icon: Clock,
            label: t('contactPage.workingHours'),
            value: t('contactPage.workingHoursValue'),
            tone: 'gray',
        },
    ]
})

const loadContactInfo = async () => {
    try {
        info.value = await contactInfoService.get()
    } catch (error) {
        console.error('Contact info load error:', error)
    }
}

const handleSubmit = async () => {
    if (form.phone && !phoneInputRef.value?.validate()) {
        errorText.value = phoneInputRef.value?.getError?.() || t('common.phoneInvalid')
        return
    }

    submitting.value = true
    errorText.value = ''

    try {
        const result = await contactService.sendContact(form)
        if (result.error) throw result.error
        submitted.value = true
    } catch (error) {
        console.error('Contact submit error:', error)
        errorText.value =
            error?.response?.data?.message ||
            'Xabar yuborishda xatolik yuz berdi'
    } finally {
        submitting.value = false
    }
}

onMounted(loadContactInfo)
</script>
