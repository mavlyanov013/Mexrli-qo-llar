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
                        <div
                            class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                            :class="item.color"
                        >
                            <component :is="item.icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ item.label }}</p>
                            <p class="font-medium text-gray-900">{{ item.value }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl overflow-hidden h-64 mt-6 bg-gray-200">
                        <iframe
                            src="https://www.openstreetmap.org/export/embed.html?bbox=69.15%2C41.26%2C69.35%2C41.36&layer=mapnik"
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
                        <div class="text-6xl text-[#4CAF50] mb-4">✓</div>
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
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    :placeholder="t('contactPage.phoneOptional')"
                                    class="rounded-xl h-11 border border-gray-300 px-4 w-full outline-none"
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
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import {
    MapPin,
    Phone,
    Mail,
    Clock,
} from 'lucide-vue-next'

const { t } = useI18n()

const submitted = ref(false)
const submitting = ref(false)
const errorText = ref('')

const form = reactive({
    full_name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
})

const contactInfo = computed(() => [
    {
        icon: MapPin,
        label: t('contactPage.address'),
        value: '123 Charity Lane, Tashkent, Uzbekistan',
        color: 'bg-blue-50 text-[#2A7DE1]',
    },
    {
        icon: Phone,
        label: t('contactPage.phone'),
        value: '+998 71 123 45 67',
        color: 'bg-green-50 text-[#4CAF50]',
    },
    {
        icon: Mail,
        label: t('contactPage.email'),
        value: 'info@mehrli.uz',
        color: 'bg-orange-50 text-[#FF9800]',
    },
    {
        icon: Clock,
        label: t('contactPage.workingHours'),
        value: t('contactPage.workingHoursValue'),
        color: 'bg-purple-50 text-purple-600',
    },
])

const handleSubmit = async () => {
    submitting.value = true
    errorText.value = ''

    try {
        await api.post('/contact-messages', form)
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
</script>
