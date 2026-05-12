<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/news/create' : ''">
        <template v-if="isListMode">
            <AdminTable :columns="columns" :rows="rows">
                <template #cell-actions="{ row }">
                    <div class="flex items-center gap-3">
                        <router-link
                            :to="`/admin/news/${row.id}`"
                            class="p-2 rounded-md hover:bg-blue-50 text-blue-600"
                            title="Ko‘rish"
                        >
                            <Eye class="w-5 h-5" />
                        </router-link>
                        <router-link :to="`/admin/news/${row.id}/edit`" class="p-2 rounded-md hover:bg-amber-50 text-amber-600" title="Tahrirlash">
                            <Pencil class="w-5 h-5" />
                        </router-link>
                        <button @click="remove(row.id)" class="p-2 rounded-md hover:bg-red-50 text-red-600" title="O‘chirish">
                            <Trash2 class="w-5 h-5" />
                        </button>
                    </div>
                </template>
            </AdminTable>
        </template>
        <template v-else-if="isViewMode">
            <div class="bg-white p-6 rounded-xl shadow">
                <button
                    @click="closeMessage"
                    class="text-sm text-blue-600 mb-3"
                >
                    ← Ortga
                </button>

                <img
                    v-if="form.cover_image"
                    :src="form.cover_image"
                    class="w-full h-60 object-cover rounded-xl mb-4"
                />

                <h1 class="text-2xl font-bold mb-2">
                    {{ form.title }}
                </h1>

                <p class="text-gray-500 mb-4">
            <span>
                🕓 Yaratilgan: {{ new Date(form.created_at).toLocaleString() }}
            </span>

                    <span v-if="form.published_at" class="ml-3">
                📅 Chop etilgan: {{ new Date(form.published_at).toLocaleString() }}
            </span>

                    <span v-else class="ml-3 text-amber-600">
                Draft
            </span>
                </p>

                <div class="whitespace-pre-line text-gray-700">
                    {{ form.content }}
                </div>

                <div class="mt-6">
                    <router-link
                        :to="`/admin/news/${route.params.id}/edit`"
                        class="btn-primary"
                    >
                        ✏️ Tahrirlash
                    </router-link>
                </div>

            </div>
        </template>

        <template v-else>
            <form class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="save">
                <input v-model="form.title" class="input md:col-span-2" placeholder="Sarlavha" required />
                <input v-model="form.slug" class="input" placeholder="Slug (ixtiyoriy)" />
                <select v-model="form.status" class="input">
                    <option value="draft">Qoralama</option>
                    <option value="published">Chop etish</option>
                </select>
                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600 mb-2 block">Muqova rasmi</label>

                    <div class="flex items-center gap-3">
                        <input
                            type="file"
                            accept="image/*"
                            class="input flex-1"
                            @change="handleFileChange"
                        />

                        <span v-if="uploading" class="text-sm text-gray-500">
            Yuklanmoqda...
        </span>
                    </div>

                    <div v-if="form.cover_image" class="mt-3">
                        <img
                            :src="form.cover_image"
                            class="h-24 w-40 object-cover rounded-lg border"
                        />
                    </div>
                </div>
                <textarea v-model="form.content" class="input md:col-span-2" rows="6" placeholder="Matn" required />
                <div class="md:col-span-2 flex gap-3 mt-2">
                    <button class="btn-primary">Saqlash</button>
                    <router-link to="/admin/news" class="btn-secondary">Bekor qilish</router-link>
                </div>
            </form>
        </template>
    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import newsService from '@/services/newsService'
import { Pencil, Trash2, Eye } from 'lucide-vue-next'

const rows = ref([])
const route = useRoute()
const router = useRouter()
const form = reactive({ title: '', slug: '', content: '', cover_image: '', status: 'draft' })
const isListMode = computed(() => route.name === 'admin-news')
const isEditMode = computed(() => route.name === 'admin-news-edit')
const title = computed(() => (isListMode.value ? 'Yangiliklar' : isEditMode.value ? 'Yangilikni tahrirlash' : 'Yangi yangilik yaratish'))
const columns = [
    { key: 'title', label: 'Sarlavha' },
    // { key: 'slug', label: 'Slug' },
    { key: 'status', label: 'Holat' },
    { key: 'published_at', label: 'Chop etilgan sana' },
    { key: 'actions', label: 'Amallar' },
]

const load = async () => {
    const res = await newsService.getAdminList()
    rows.value = res.data ?? []
}

const closeMessage = () => {
    router.back()
}

const resetForm = () => {
    Object.assign(form, { title: '', slug: '', content: '', cover_image: '', status: 'draft' })
}
const uploading = ref(false)

const isViewMode = computed(() => route.name === 'admin-news-view')

const handleFileChange = async (e) => {
    const file = e.target.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append('file', file)

    uploading.value = true

    try {
        const res = await newsService.uploadImage(formData)
        form.cover_image = res.data.url
    } finally {
        uploading.value = false
    }
}

const loadCurrent = async () => {
    if (!route.params.id) return

    const res = await newsService.getById(route.params.id)
    const row = res.data

    if (!row) return

    Object.assign(form, {
        title: row.title || '',
        slug: row.slug || '',
        content: row.content || '',
        cover_image: row.cover_image || '',
        status: row.status || 'draft'
    })
}

const save = async () => {
    if (isEditMode.value) await newsService.update(route.params.id, form)
    else await newsService.create(form)
    await router.push('/admin/news')
}

const remove = async (id) => {
    if (!confirm('Yangilik o‘chirilsinmi?')) return
    await newsService.remove(id)
    await load()
}

const hydrate = async () => {
    if (isListMode.value) {
        await load()
    }

    else if (isEditMode.value || isViewMode.value) {
        await loadCurrent()
    }

    else {
        resetForm()
    }
}

onMounted(hydrate)
watch(() => route.fullPath, hydrate)
</script>

<style scoped>
.input { border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 14px; }
.btn-primary { background: #2A7DE1; color: #fff; border-radius: 12px; padding: 10px 18px; }
.btn-secondary { border: 1px solid #ddd; border-radius: 12px; padding: 10px 18px; }
</style>
