<template>
    <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-6">
            <UserPlus class="w-5 h-5 text-blue-600" />
            <h2 class="text-xl font-semibold text-gray-900">
                {{ t('admin.usersModule.createUser') }}
            </h2>
        </div>

        <UserForm @submit="handleSubmit" />
    </section>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { UserPlus } from 'lucide-vue-next'
import userService from '@/services/userService'
import UserForm from './components/UserForm.vue'

const { t } = useI18n()
const router = useRouter()

const handleSubmit = async (payload) => {
    await userService.create(payload)
    await router.push('/admin/users')
}
</script>
