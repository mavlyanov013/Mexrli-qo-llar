<template>
    <form @submit.prevent="submit" class="form">
        <h2>{{ t('donationForm.title') }}</h2>

        <input
            v-model="form.donor_name"
            type="text"
            :placeholder="t('donationForm.namePlaceholder')"
        />

        <input
            v-model="form.donor_email"
            type="email"
            :placeholder="t('donationForm.emailPlaceholder')"
        />

        <input
            v-model.number="form.amount"
            type="number"
            min="1"
            :placeholder="t('donationForm.amountPlaceholder')"
        />

        <select v-model="form.type">
            <option value="one_time">{{ t('donationForm.oneTime') }}</option>
            <option value="monthly">{{ t('donationForm.monthly') }}</option>
        </select>

        <textarea
            v-model="form.message"
            :placeholder="t('donationForm.notePlaceholder')"
        ></textarea>

        <label>
            <input v-model="form.is_anonymous" type="checkbox" />
            {{ t('donationForm.anonymous') }}
        </label>

        <button type="submit" :disabled="loading">
            {{ loading ? t('donationForm.submitting') : t('donationForm.donate') }}
        </button>

        <p v-if="success" class="success">{{ success }}</p>
        <p v-if="error" class="error">{{ error }}</p>
    </form>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { createDonation } from '../services/donationService'

const { t } = useI18n()

const props = defineProps({
    caseId: {
        type: Number,
        default: null,
    },
})

const form = reactive({
    case_id: props.caseId,
    donor_name: '',
    donor_email: '',
    amount: null,
    currency: 'USD',
    type: 'one_time',
    message: '',
    is_anonymous: false,
})

watch(
    () => props.caseId,
    (value) => {
        form.case_id = value
    },
    { immediate: true }
)

const loading = ref(false)
const success = ref('')
const error = ref('')

const submit = async () => {
    loading.value = true
    success.value = ''
    error.value = ''

    try {
        await createDonation(form)
        success.value = t('donationForm.success')

        form.donor_name = ''
        form.donor_email = ''
        form.amount = null
        form.type = 'one_time'
        form.message = ''
        form.is_anonymous = false
    } catch (e) {
        error.value = e.response?.data?.message || t('donationForm.error')
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 24px;
}

input,
select,
textarea,
button {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.success {
    color: green;
}

.error {
    color: red;
}
</style>
