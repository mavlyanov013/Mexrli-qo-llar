<template>
    <section class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="mb-4 text-xl font-semibold">{{ t('admin.usersModule.editUser') }}</h2>
        <UserForm v-if="user" :initial-values="user" is-edit @submit="handleSubmit" />
        <p v-else class="text-sm text-gray-500">{{ t('admin.loading') }}</p>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import userService from '@/services/userService'
import UserForm from './components/UserForm.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const user = ref(null)

onMounted(async () => {
    const res = await userService.getById(route.params.id)
    user.value = res.data
})

const handleSubmit = async (payload) => {
    await userService.update(route.params.id, payload)
    await router.push('/admin/users')
}
</script>
