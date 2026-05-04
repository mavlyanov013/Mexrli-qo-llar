<template>
    <section class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="mb-4 text-xl font-semibold">{{ t('admin.usersModule.userDetails') }}</h2>
        <div v-if="user" class="space-y-2 text-sm">
            <p><strong>{{ t('admin.name') }}:</strong> {{ user.name }}</p>
            <p><strong>{{ t('admin.email') }}:</strong> {{ user.email }}</p>
            <p><strong>{{ t('admin.role') }}:</strong> {{ user.role }}</p>
        </div>
        <p v-else class="text-sm text-gray-500">{{ t('admin.loading') }}</p>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import userService from '@/services/userService'

const { t } = useI18n()
const route = useRoute()
const user = ref(null)

onMounted(async () => {
    const res = await userService.getById(route.params.id)
    user.value = res.data
})
</script>
