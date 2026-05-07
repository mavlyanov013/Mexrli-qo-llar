<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/news/create' : ''">
        <template v-if="isListMode">
            <AdminTable :columns="columns" :rows="rows">
                <template #cell-actions="{ row }">
                    <div class="flex items-center gap-3">
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

        <template v-else>
            <form class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="save">
                <input v-model="form.title" class="input md:col-span-2" placeholder="Sarlavha" required />
                <input v-model="form.slug" class="input" placeholder="Slug (ixtiyoriy)" />
                <select v-model="form.status" class="input">
                    <option value="draft">Qoralama</option>
                    <option value="published">Chop etish</option>
                </select>
                <input v-model="form.cover_image" class="input md:col-span-2" placeholder="Muqova rasmi URL" />
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
import { Pencil, Trash2 } from 'lucide-vue-next'

const rows = ref([])
const route = useRoute()
const router = useRouter()
const form = reactive({ title: '', slug: '', content: '', cover_image: '', status: 'draft' })
const isListMode = computed(() => route.name === 'admin-news')
const isEditMode = computed(() => route.name === 'admin-news-edit')
const title = computed(() => (isListMode.value ? 'Yangiliklar' : isEditMode.value ? 'Yangilikni tahrirlash' : 'Yangi yangilik yaratish'))
const columns = [
    { key: 'title', label: 'Sarlavha' },
    { key: 'slug', label: 'Slug' },
    { key: 'status', label: 'Holat' },
    { key: 'published_at', label: 'Chop etilgan sana' },
    { key: 'actions', label: 'Amallar' },
]

const load = async () => {
    const res = await newsService.getAdminList()
    rows.value = res.data ?? []
}

const resetForm = () => {
    Object.assign(form, { title: '', slug: '', content: '', cover_image: '', status: 'draft' })
}

const loadCurrent = async () => {
    if (!route.params.id) return
    const res = await newsService.getAdminList()
    const row = (res.data ?? []).find((item) => String(item.id) === String(route.params.id))
    if (!row) return
    Object.assign(form, { title: row.title || '', slug: row.slug || '', content: row.content || '', cover_image: row.cover_image || '', status: row.status || 'draft' })
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
    if (isListMode.value) await load()
    else if (isEditMode.value) await loadCurrent()
    else resetForm()
}

onMounted(hydrate)
watch(() => route.fullPath, hydrate)
</script>

<style scoped>
.input { border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 14px; }
.btn-primary { background: #2A7DE1; color: #fff; border-radius: 12px; padding: 10px 18px; }
.btn-secondary { border: 1px solid #ddd; border-radius: 12px; padding: 10px 18px; }
</style>
