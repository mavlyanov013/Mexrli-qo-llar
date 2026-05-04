<template>
    <AdminCrudShell :title="t('admin.pages')">
        <div class="space-y-3">
            <p v-if="loading" class="text-sm text-gray-500">{{ t('admin.loading') }}</p>
            <div v-for="page in pages" :key="page.id" class="rounded-lg border border-gray-100 p-3">
                <p class="font-medium">{{ page.title }}</p>
                <p class="text-xs text-gray-500">/{{ page.slug }} • {{ page.is_active ? 'published' : 'draft' }}</p>
            </div>
            <AdminEmptyState v-if="!loading && pages.length === 0" :message="t('admin.noData')" />
        </div>
    </AdminCrudShell>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import adminPageService from '@/services/adminPageService'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminEmptyState from '@/admin/components/common/AdminEmptyState.vue'

const { t } = useI18n()
const loading = ref(false)
const pages = ref([])

onMounted(async () => {
    loading.value = true
    try {
        const res = await adminPageService.getAll()
        pages.value = res.data || []
    } finally {
        loading.value = false
    }
})
</script>
