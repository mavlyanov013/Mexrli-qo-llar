<template>
    <div class="auth-page">
        <div class="auth-overlay" />
        <div class="auth-card">
            <div class="auth-header">
                <img
                    :src="siteLogo"
                    :alt="t('common.brandName')"
                    class="auth-logo"
                />

                <p class="auth-badge">{{ t('common.brandName') }}</p>

                <h1>{{ t('auth.login.title') }}</h1>
                <p class="auth-subtitle">
                    {{ t('auth.login.subtitle') }}
                </p>
            </div>

            <form @submit.prevent="submit" class="auth-form">
                <div class="form-group">
                    <label for="email">{{ t('auth.login.email') }}</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        :placeholder="t('auth.login.emailPlaceholder')"
                        autocomplete="email"
                    />
                </div>

                <div class="form-group">
                    <label for="password">{{ t('auth.login.password') }}</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :placeholder="t('auth.login.passwordPlaceholder')"
                        autocomplete="current-password"
                    />
                </div>

                <p v-if="route.query.session === 'expired'" class="error-message">
                    {{ t('auth.login.sessionExpired') }}
                </p>

                <p v-if="error" class="error-message">{{ error }}</p>

                <button type="submit" class="auth-button">
                    {{ t('auth.login.submit') }}
                </button>

<!--                <p class="auth-link">-->
<!--                    Akkountingiz yo‘qmi?-->
<!--                    <router-link to="/register">Ro‘yxatdan o‘tish</router-link>-->
<!--                </p>-->
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { siteLogo } from '@/constants/branding'
import { useAuth } from '../composables/useAuth'

const { login } = useAuth()
const { t } = useI18n()
const route = useRoute()

const form = reactive({
    email: '',
    password: '',
})

const error = ref(null)

const submit = async () => {
    error.value = null

    try {
        await login(form)
    } catch (e) {
        error.value = e.response?.data?.message || t('auth.login.invalidCredentials')
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
    max-width: 460px;
    background: rgba(255, 255, 255, 0.92);
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

.error-message {
    margin: 0;
    padding: 12px 14px;
    border-radius: 12px;
    background: #fff1f2;
    color: #dc2626;
    font-size: 14px;
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
.auth-logo {
    width: 72px;
    height: 72px;
    object-fit: contain;
    margin: 0 auto 14px;
}
</style>
