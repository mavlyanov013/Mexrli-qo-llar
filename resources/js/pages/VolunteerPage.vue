<template>
    <div class="pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ t('volunteerPage.title') }}
                </h1>
                <p class="text-lg text-gray-500">
                    {{ t('volunteerPage.subtitle') }}
                </p>
            </div>

            <SectionHeader :title="t('volunteerPage.rolesTitle')" />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-20">
                <div
                    v-for="(role, index) in roles"
                    :key="index"
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm"
                >
                    <IconBadge
                        :icon="role.icon"
                        :tone="role.tone"
                        size="md"
                        class="mb-4"
                    />
                    <h3 class="font-bold text-gray-900 mb-2">{{ role.title }}</h3>
                    <p class="text-sm text-gray-500">{{ role.desc }}</p>
                </div>
            </div>

            <div class="max-w-2xl mx-auto">
                <div
                    v-if="submitted"
                    class="text-center py-12 bg-white rounded-2xl border border-gray-100"
                >
                    <IconBadge :icon="CircleCheck" tone="green" size="lg" class="mx-auto mb-4" />
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        {{ t('volunteerPage.successTitle') }}
                    </h2>
                    <p class="text-gray-500">
                        {{ t('volunteerPage.successText') }}
                    </p>
                </div>

                <div v-else class="bg-white rounded-2xl p-8 border border-gray-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">
                        {{ t('volunteerPage.formTitle') }}
                    </h2>

                    <form class="space-y-4" @submit.prevent="handleSubmit">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <input
                                v-model="form.full_name"
                                type="text"
                                :placeholder="t('volunteerPage.form.fullName')"
                                class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none"
                                required
                            />
                            <input
                                v-model="form.email"
                                type="email"
                                :placeholder="t('volunteerPage.form.email')"
                                class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none"
                                required
                            />
                            <PhoneInput
                                ref="phoneInputRef"
                                v-model="form.phone"
                                required
                                input-class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none"
                            />
                            <input
                                v-model="form.city"
                                type="text"
                                :placeholder="t('volunteerPage.form.city')"
                                class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none"
                            />
                        </div>

                        <select
                            v-model="form.role_interest"
                            class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none"
                        >
                            <option
                                v-for="role in roles"
                                :key="role.value"
                                :value="role.value"
                            >
                                {{ role.title }}
                            </option>
                            <option value="other">{{ t('volunteerPage.form.other') }}</option>
                        </select>

<!--                        <select-->
<!--                            v-model="form.availability"-->
<!--                            class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none"-->
<!--                        >-->
<!--                            <option value="full_time">{{ t('volunteerPage.form.availabilityOptions.fullTime') }}</option>-->
<!--                            <option value="part_time">{{ t('volunteerPage.form.availabilityOptions.partTime') }}</option>-->
<!--                            <option value="weekends">{{ t('volunteerPage.form.availabilityOptions.weekends') }}</option>-->
<!--                            <option value="flexible">{{ t('volunteerPage.form.availabilityOptions.flexible') }}</option>-->
<!--                        </select>-->

                        <textarea
                            v-model="form.experience"
                            rows="3"
                            :placeholder="t('volunteerPage.form.experience')"
                            class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none resize-none"
                        />

                        <textarea
                            v-model="form.motivation"
                            rows="3"
                            :placeholder="t('volunteerPage.form.motivation')"
                            class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none resize-none"
                        />

                        <button
                            type="submit"
                            :disabled="submitting"
                            class="w-full h-12 bg-[#2A7DE1] hover:bg-[#1E6BC9] text-white rounded-xl font-semibold disabled:opacity-60"
                        >
                            {{ submitting ? t('volunteerPage.form.submitting') : t('volunteerPage.form.submit') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import {
    CircleCheck,
    Heart,
    Megaphone,
    Calendar,
    Languages,
    Share2,
    Users,
} from 'lucide-vue-next'
import SectionHeader from '../components/shared/SectionHeader.vue'
import IconBadge from '../components/shared/IconBadge.vue'
import { useVolunteerApplications } from '@/composables/useVolunteerApplications'
import PhoneInput from '../components/shared/PhoneInput.vue'

const { t } = useI18n()

const submitted = ref(false)
const phoneInputRef = ref(null)
const { loading: submitting, submitApplication } = useVolunteerApplications()

const roles = computed(() => [
    {
        icon: Heart,
        tone: 'blue',
        title: t('volunteerPage.roles.medicalSupport.title'),
        desc: t('volunteerPage.roles.medicalSupport.desc'),
        value: 'medical_support',
    },
    {
        icon: Megaphone,
        tone: 'orange',
        title: t('volunteerPage.roles.fundraising.title'),
        desc: t('volunteerPage.roles.fundraising.desc'),
        value: 'fundraising',
    },
    {
        icon: Calendar,
        tone: 'green',
        title: t('volunteerPage.roles.events.title'),
        desc: t('volunteerPage.roles.events.desc'),
        value: 'events',
    },
    {
        icon: Languages,
        tone: 'yellow',
        title: t('volunteerPage.roles.translation.title'),
        desc: t('volunteerPage.roles.translation.desc'),
        value: 'translation',
    },
    {
        icon: Share2,
        tone: 'red',
        title: t('volunteerPage.roles.socialMedia.title'),
        desc: t('volunteerPage.roles.socialMedia.desc'),
        value: 'social_media',
    },
    {
        icon: Users,
        tone: 'blue',
        title: t('volunteerPage.roles.mentoring.title'),
        desc: t('volunteerPage.roles.mentoring.desc'),
        value: 'mentoring',
    },
])

const form = reactive({
    full_name: '',
    email: '',
    phone: '',
    city: '',
    role_interest: 'other',
    experience: '',
    motivation: '',
    availability: 'flexible',
})

const handleSubmit = async () => {
    if (!phoneInputRef.value?.validate()) {
        return
    }

    const result = await submitApplication(form)
    if (!result.error) {
        submitted.value = true
    }
}
</script>
