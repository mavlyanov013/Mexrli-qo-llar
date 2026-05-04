<template>
    <AdminCrudShell :title="t('admin.settings')">
        <form class="space-y-3" @submit.prevent="save">
            <div v-for="(item, index) in items" :key="index" class="grid grid-cols-2 gap-3">
                <input v-model="item.key" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" placeholder="key" />
                <input v-model="item.value" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" placeholder="value" />
            </div>
            <div class="flex gap-2">
                <button type="button" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" @click="items.push({ key: '', value: '' })">
                    +
                </button>
                <button type="submit" class="rounded-lg bg-[#2A7DE1] px-4 py-2 text-sm text-white">{{ t('admin.save') }}</button>
            </div>
        </form>
    </AdminCrudShell>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import settingsService from '@/services/settingsService'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'

const { t } = useI18n()
const items = ref([])

onMounted(async () => {
    const res = await settingsService.getAll()
    items.value = (res.data || []).map((item) => ({ key: item.key, value: item.value ?? '' }))
})

const save = async () => {
    await settingsService.update(items.value.filter((item) => item.key))
}
</script>
