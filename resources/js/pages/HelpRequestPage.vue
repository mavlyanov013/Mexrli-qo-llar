<template>
    <div class="pt-24 pb-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-10">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-50 flex items-center justify-center text-[#2A7DE1] text-3xl">
                    ❤
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                    {{ t('helpRequestPage.title') }}
                </h1>
                <p class="text-gray-500 mt-2">
                    {{ t('helpRequestPage.subtitle') }}
                </p>
            </div>

            <div
                v-if="submitted"
                class="max-w-2xl mx-auto text-center py-20"
            >
                <div class="text-6xl text-[#4CAF50] mx-auto mb-6">✓</div>
                <h2 class="text-3xl font-bold text-gray-900 mb-3">
                    {{ t('helpRequestPage.successTitle') }}
                </h2>
                <p class="text-gray-600 mb-2">
                    {{ t('helpRequestPage.successText1') }}
                </p>
                <p class="text-gray-600">
                    {{ t('helpRequestPage.successText2') }}
                </p>
            </div>

            <form
                v-else
                class="bg-white rounded-2xl p-8 border border-gray-100 space-y-6"
                @submit.prevent="handleSubmit"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t('helpRequestPage.fullName') }}
                        </label>
                        <input
                            v-model="form.full_name"
                            type="text"
                            class="rounded-xl mt-2 border border-gray-300 px-4 py-3 w-full outline-none"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t('helpRequestPage.phone') }}
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="rounded-xl mt-2 border border-gray-300 px-4 py-3 w-full outline-none"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t('helpRequestPage.city') }}
                    </label>
                    <input
                        v-model="form.city"
                        type="text"
                        class="rounded-xl mt-2 border border-gray-300 px-4 py-3 w-full outline-none"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t('helpRequestPage.supportType') }}
                    </label>
                    <select
                        v-model="form.support_type"
                        class="rounded-xl mt-2 border border-gray-300 px-4 py-3 w-full outline-none"
                    >
                        <option value="medical_treatment">{{ t('helpRequestPage.supportOptions.medical_treatment') }}</option>
                        <option value="surgery">{{ t('helpRequestPage.supportOptions.surgery') }}</option>
                        <option value="rehabilitation">{{ t('helpRequestPage.supportOptions.rehabilitation') }}</option>
                        <option value="medication">{{ t('helpRequestPage.supportOptions.medication') }}</option>
                        <option value="family_support">{{ t('helpRequestPage.supportOptions.family_support') }}</option>
                        <option value="other">{{ t('helpRequestPage.supportOptions.other') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t('helpRequestPage.situation') }}
                    </label>
                    <textarea
                        v-model="form.situation_description"
                        rows="6"
                        :placeholder="t('helpRequestPage.situationPlaceholder')"
                        class="rounded-xl mt-2 border border-gray-300 px-4 py-3 w-full outline-none resize-none"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t('helpRequestPage.medicalDocuments') }}
                    </label>
                    <div class="mt-2 border-2 border-dashed border-gray-200 rounded-xl p-6 text-center">
                        <div class="text-3xl text-gray-400 mx-auto mb-2">📄</div>
                        <p class="text-sm text-gray-500 mb-3">
                            {{ t('helpRequestPage.medicalDocumentsHint') }}
                        </p>
                        <input
                            type="file"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png"
                            @change="handleLocalFiles($event, 'medical_documents')"
                        />
                        <p
                            v-if="form.medical_documents.length > 0"
                            class="text-sm text-green-600 mt-2"
                        >
                            {{ t('helpRequestPage.filesSelected', { count: form.medical_documents.length }) }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t('helpRequestPage.photos') }}
                    </label>
                    <div class="mt-2 border-2 border-dashed border-gray-200 rounded-xl p-6 text-center">
                        <div class="text-3xl text-gray-400 mx-auto mb-2">⤴</div>
                        <p class="text-sm text-gray-500 mb-3">
                            {{ t('helpRequestPage.photosHint') }}
                        </p>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleLocalFiles($event, 'photos')"
                        />
                        <p
                            v-if="form.photos.length > 0"
                            class="text-sm text-green-600 mt-2"
                        >
                            {{ t('helpRequestPage.photosSelected', { count: form.photos.length }) }}
                        </p>
                    </div>
                </div>

                <label class="flex items-start gap-3 bg-gray-50 p-4 rounded-xl">
                    <input
                        v-model="form.consent_given"
                        type="checkbox"
                        class="mt-1"
                    />
                    <span class="text-sm text-gray-600 leading-relaxed">
                        {{ t('helpRequestPage.consent') }}
                    </span>
                </label>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="w-full h-14 bg-[#2A7DE1] hover:bg-[#1E6BC9] text-white rounded-xl gap-2 text-lg font-semibold disabled:opacity-60"
                >
                    {{ submitting ? t('helpRequestPage.submitting') : t('helpRequestPage.submit') }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const { t } = useI18n()

const submitted = ref(false)
const submitting = ref(false)

const form = reactive({
    full_name: '',
    phone: '',
    city: '',
    situation_description: '',
    support_type: 'medical_treatment',
    medical_documents: [],
    photos: [],
    consent_given: false,
})

const handleLocalFiles = (event, field) => {
    const files = Array.from(event.target.files || [])
    form[field] = files.map((file) => file.name)
}

const handleSubmit = async () => {
    if (!form.consent_given) {
        alert(t('helpRequestPage.consentAlert'))
        return
    }

    submitting.value = true

    try {
        await api.post('/help-requests', form)
        submitted.value = true
    } catch (error) {
        console.error('Help request submit error:', error)
    } finally {
        submitting.value = false
    }
}
</script>
