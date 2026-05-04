<template>
    <section class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="mb-4 text-xl font-semibold">{{ t('admin.usersModule.createUser') }}</h2>
        <UserForm @submit="handleSubmit" />
    </section>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import userService from '@/services/userService'
import UserForm from './components/UserForm.vue'

const { t } = useI18n()
const router = useRouter()

const handleSubmit = async (payload) => {
    await userService.create(payload)
    await router.push('/admin/users')
}
</script>
