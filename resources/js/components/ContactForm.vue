<template>
    <form @submit.prevent="submit">
        <input v-model="form.full_name" :placeholder="t('forms.fullName')" />
        <PhoneInput ref="phoneInputRef" v-model="form.phone" input-class="w-full" />
        <input v-model="form.email" :placeholder="t('forms.email')" />
        <textarea v-model="form.message" :placeholder="t('forms.message')"></textarea>

        <button type="submit">{{ t('forms.send') }}</button>

        <p v-if="success">{{ success }}</p>
        <p v-if="error">{{ error }}</p>
    </form>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { sendContact } from '../services/contactService'
import PhoneInput from './shared/PhoneInput.vue'

const { t } = useI18n()

const form = reactive({
    full_name: '',
    phone: '',
    email: '',
    message: '',
})

const success = ref(null)
const error = ref(null)
const phoneInputRef = ref(null)

const submit = async () => {
    success.value = null
    error.value = null

    if (form.phone && !phoneInputRef.value?.validate()) {
        error.value = phoneInputRef.value?.getError?.() || t('common.phoneInvalid')
        return
    }

    try {
        await sendContact(form)
        success.value = t('forms.sent')

        form.full_name = ''
        form.phone = ''
        form.email = ''
        form.message = ''
    } catch (e) {
        error.value = e.response?.data?.message || t('forms.error')
    }
}
</script>
