<template>
    <div class="auth-page">
        <div class="auth-overlay" />
        <div class="auth-card">
            <div class="auth-header">
                <p class="auth-badge">{{ t('common.brandName') }}</p>
                <h1>{{ t('auth.register.title') }}</h1>
                <p class="auth-subtitle">
                    {{ t('auth.register.subtitle') }}
                </p>
            </div>

            <form @submit.prevent="submit" class="auth-form">
                <div class="form-group">
                    <label for="name">{{ t('auth.register.name') }}</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        :placeholder="t('auth.register.namePlaceholder')"
                        autocomplete="name"
                    />
                    <small v-if="errors.name">{{ errors.name[0] }}</small>
                </div>

                <div class="form-group">
                    <label for="email">{{ t('auth.register.email') }}</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        :placeholder="t('auth.register.emailPlaceholder')"
                        autocomplete="email"
                    />
                    <small v-if="errors.email">{{ errors.email[0] }}</small>
                </div>

                <div class="form-group">
                    <label for="password">{{ t('auth.register.password') }}</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :placeholder="t('auth.register.passwordPlaceholder')"
                        autocomplete="new-password"
                    />
                    <small v-if="errors.password">{{ errors.password[0] }}</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">{{ t('auth.register.passwordConfirmation') }}</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        :placeholder="t('auth.register.passwordConfirmationPlaceholder')"
                        autocomplete="new-password"
                    />
                </div>

                <p v-if="message" class="success-message">{{ message }}</p>
                <p v-if="error" class="error-message">{{ error }}</p>

                <button type="submit" class="auth-button" :disabled="loading">
                    {{ loading ? t('auth.register.submitting') : t('auth.register.submit') }}
                </button>

                <p class="auth-link">
                    {{ t('auth.register.alreadyHaveAccount') }}
                    <router-link to="/login">{{ t('auth.register.loginLink') }}</router-link>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuth } from '../composables/useAuth'

const { register } = useAuth()
const { t } = useI18n()

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const loading = ref(false)
const message = ref('')
const error = ref('')
const errors = ref({})

const submit = async () => {
    loading.value = true
    message.value = ''
    error.value = ''
    errors.value = {}

    try {
        await register(form)
        message.value = t('auth.register.success')
    } catch (e) {
        if (e.response?.status === 422) {
            error.value = e.response.data.message || t('auth.register.validationError')
            errors.value = e.response.data.errors || {}
        } else {
            error.value = e.response?.data?.message || t('auth.register.genericError')
        }
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    position: relative;
    overflow: hidden;
    background:
        linear-gradient(rgba(255, 248, 240, 0.82), rgba(255, 248, 240, 0.92)),
        url('https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=1400&q=80')
        center/cover no-repeat;
}

.auth-overlay {
    position: absolute;
    inset: 0;
    backdrop-filter: blur(2px);
}

.auth-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(255, 152, 0, 0.15);
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 20px 60px rgba(30, 41, 59, 0.12);
}

.auth-header {
    margin-bottom: 24px;
    text-align: center;
}

.auth-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 999px;
    background: #fff3e0;
    color: #ff9800;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 12px;
}

.auth-header h1 {
    margin: 0 0 10px;
    font-size: 32px;
    line-height: 1.2;
    color: #1e293b;
}

.auth-subtitle {
    margin: 0;
    color: #64748b;
    font-size: 15px;
    line-height: 1.6;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

label {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
}

input {
    width: 100%;
    padding: 13px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    outline: none;
    background: #fff;
    font-size: 15px;
    transition: 0.2s ease;
    box-sizing: border-box;
}

input:focus {
    border-color: #ff9800;
    box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.12);
}

.auth-button {
    margin-top: 6px;
    border: none;
    border-radius: 14px;
    padding: 14px;
    background: linear-gradient(135deg, #ff9800, #f97316);
    color: white;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
}

.auth-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(249, 115, 22, 0.22);
}

.auth-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.success-message {
    margin: 0;
    padding: 12px 14px;
    border-radius: 12px;
    background: #ecfdf5;
    color: #047857;
    font-size: 14px;
}

.error-message,
small {
    color: #dc2626;
}

.error-message {
    margin: 0;
    padding: 12px 14px;
    border-radius: 12px;
    background: #fff1f2;
    font-size: 14px;
}

small {
    font-size: 12px;
}

.auth-link {
    margin: 4px 0 0;
    text-align: center;
    color: #64748b;
    font-size: 14px;
}

.auth-link a {
    color: #f97316;
    font-weight: 700;
    text-decoration: none;
}

.auth-link a:hover {
    text-decoration: underline;
}
</style>
