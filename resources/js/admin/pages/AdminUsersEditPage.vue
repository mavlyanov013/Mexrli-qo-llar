<template>
    <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-6">
            <UserCog class="w-5 h-5 text-amber-600" />
            <h2 class="text-xl font-semibold text-gray-900">
                {{ t('admin.usersModule.editUser') }}
            </h2>
        </div>

        <p v-if="error" class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ error }}</p>

        <div v-if="user">
            <UserForm
                :initial-values="user"
                :submitting="submitting"
                is-edit
                @submit="handleSubmit"
            />
        </div>

        <div v-else class="flex items-center gap-2 text-sm text-gray-500">
            <Loader2 class="w-4 h-4 animate-spin" />
            {{ t('admin.loading') }}
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { UserCog, Loader2 } from 'lucide-vue-next'
import userService from '@/services/userService'
import UserForm from './components/UserForm.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const user = ref(null)
const error = ref('')
const submitting = ref(false)

const extractError = (err) => {
    const data = err?.response?.data
    if (data?.errors) {
        return Object.values(data.errors).flat().join('\n')
    }
    return data?.message || err?.message || 'Saqlashda xatolik yuz berdi'
}

onMounted(async () => {
    try {
        const res = await userService.getById(route.params.id)
        user.value = res.data
    } catch (err) {
        error.value = extractError(err)
    }
})

const handleSubmit = async (payload) => {
    error.value = ''
    submitting.value = true
    try {
        await userService.update(route.params.id, payload)
        await router.push('/admin/users')
    } catch (err) {
        error.value = extractError(err)
    } finally {
        submitting.value = false
    }
}
</script>
