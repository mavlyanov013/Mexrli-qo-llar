<template>
    <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-6">
            <UserPlus class="w-5 h-5 text-blue-600" />
            <h2 class="text-xl font-semibold text-gray-900">
                {{ t('admin.usersModule.createUser') }}
            </h2>
        </div>

        <p v-if="error" class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ error }}</p>

        <UserForm :submitting="submitting" @submit="handleSubmit" />
    </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { UserPlus } from 'lucide-vue-next'
import userService from '@/services/userService'
import UserForm from './components/UserForm.vue'

const { t } = useI18n()
const router = useRouter()
const error = ref('')
const submitting = ref(false)

const extractError = (err) => {
    const data = err?.response?.data
    if (data?.errors) {
        return Object.values(data.errors).flat().join('\n')
    }
    return data?.message || err?.message || 'Saqlashda xatolik yuz berdi'
}

const handleSubmit = async (payload) => {
    error.value = ''
    submitting.value = true
    try {
        await userService.create(payload)
        await router.push('/admin/users')
    } catch (err) {
        error.value = extractError(err)
    } finally {
        submitting.value = false
    }
}
</script>
