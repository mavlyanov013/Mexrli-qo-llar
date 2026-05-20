<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/faq/create' : ''">
        <template v-if="isListMode">
            <AdminTable :columns="columns" :rows="rows">
                <template #cell-actions="{ row }">
                    <div class="flex items-center gap-3">
                        <router-link :to="`/admin/faq/${row.id}/edit`" class="p-2 rounded-md hover:bg-amber-50 text-amber-600" title="Tahrirlash">
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
                <input v-model="form.question" class="input md:col-span-2" placeholder="Savol" required />
                <input v-model="form.category" class="input" placeholder="Kategoriya" />
                <input v-model.number="form.order" type="number" class="input" placeholder="Tartib" />
                <textarea v-model="form.answer" class="input md:col-span-2" rows="5" placeholder="Javob" required />
                <label class="text-sm flex items-center gap-2 md:col-span-2"><input v-model="form.is_active" type="checkbox" /> Faol</label>
                <div class="md:col-span-2 flex gap-3 mt-2">
                    <button class="btn-primary">Saqlash</button>
                    <router-link to="/admin/faq" class="btn-secondary">Bekor qilish</router-link>
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
import faqService from '@/services/faqService'
import { Pencil, Trash2 } from 'lucide-vue-next'

const rows = ref([])
const route = useRoute()
const router = useRouter()
const form = reactive({ question: '', answer: '', category: '', order: 0, is_active: true })
const isListMode = computed(() => route.name === 'admin-faq')
const isEditMode = computed(() => route.name === 'admin-faq-edit')
const title = computed(() => (isListMode.value ? 'FAQ' : isEditMode.value ? 'Savolni tahrirlash' : 'Yangi savol qo‘shish'))
const columns = [
    { key: 'question', label: 'Savol' },
    { key: 'category', label: 'Kategoriya' },
    { key: 'order', label: 'Tartib' },
    { key: 'is_active', label: 'Holat' },
    { key: 'actions', label: 'Amallar' },
]

const load = async () => {
    const res = await faqService.getAdminList()
    rows.value = res.data ?? []
}

const resetForm = () => {
    Object.assign(form, { question: '', answer: '', category: '', order: 0, is_active: true })
}

const loadCurrent = async () => {
    if (!route.params.id) return
    const res = await faqService.getAdminList()
    const row = (res.data ?? []).find((item) => String(item.id) === String(route.params.id))
    if (!row) return
    Object.assign(form, { question: row.question || '', answer: row.answer || '', category: row.category || '', order: row.order || 0, is_active: Boolean(row.is_active) })
}

const save = async () => {
    if (isEditMode.value) await faqService.update(route.params.id, form)
    else await faqService.create(form)
    await router.push('/admin/faq')
}

const remove = async (id) => {
    if (!confirm('Savol o‘chirilsinmi?')) return
    await faqService.remove(id)
    await load()
}

const move = async (row, direction) => {
    const sorted = [...rows.value].sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
    const idx = sorted.findIndex((item) => item.id === row.id)
    const target = idx + direction
    if (idx < 0 || target < 0 || target >= sorted.length) return
    const other = sorted[target]
    await faqService.update(row.id, { order: other.order ?? 0 })
    await faqService.update(other.id, { order: row.order ?? 0 })
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
