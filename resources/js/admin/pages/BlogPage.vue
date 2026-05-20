<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { useAdminStore } from '@/admin/stores/useAdminStore'
import AdminSearchInput from '@/admin/components/common/AdminSearchInput.vue'

const { t } = useI18n()
const search = ref('')
const store = useAdminStore()
const { loading, posts } = storeToRefs(store)

onMounted(() => {
    store.loadAll()
})

const filteredPosts = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return posts.value

    return posts.value.filter((post) =>
        String(post.title || '').toLowerCase().includes(q) ||
        String(post.category || '').toLowerCase().includes(q)
    )
})
</script>

<template>
    <div class="space-y-4">
        <AdminSearchInput v-model="search" :placeholder="t('admin.search') || 'Qidiruv...'" />

        <div v-if="loading" class="text-gray-500">
            {{ t('admin.loading') }}
        </div>

        <div v-else class="space-y-3">
            <h2 class="text-2xl font-bold text-gray-900">{{ t('admin.blog') }} ({{ filteredPosts.length }})</h2>

            <div
                v-for="post in filteredPosts"
                :key="post.id"
                class="bg-white rounded-2xl border border-gray-100 p-5"
            >
                <p class="font-semibold">{{ post.title }}</p>
                <p class="text-sm text-gray-500">{{ t('admin.category') }}: {{ post.category }}</p>
            </div>

            <div v-if="filteredPosts.length === 0" class="text-sm text-gray-500">
                {{ t('admin.noBlogPosts') }}
            </div>
        </div>
    </div>
</template>
