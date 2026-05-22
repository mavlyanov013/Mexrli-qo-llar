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

            <AdminPagination
                v-if="meta && meta.last_page > 1"
                :current-page="meta.current_page || 1"
                :last-page="meta.last_page || 1"
                :summary="`${meta.total || 0} ta savol`"
                @change="fetchPage"
            />
        </template>

        <template v-else>
            <form class="bg-white p-6 rounded-xl shadow space-y-4" @submit.prevent="save">
                <LocalizedFieldTabs
                    v-model="form"
                    :fields="faqLocalizedFields"
                />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input v-model="form.category" class="input" placeholder="Kategoriya" />
                    <input v-model.number="form.order" type="number" class="input" placeholder="Tartib" />
                </div>
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
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import faqService from '@/services/faqService'
import LocalizedFieldTabs from '@/admin/components/common/LocalizedFieldTabs.vue'
import { emptyLocalizedFields, assignLocalizedFromRow, validateAdminLocalizedFields, buildAdminPayload } from '@/utils/localizedContent'
import { Pencil, Trash2 } from 'lucide-vue-next'

const faqLocalizedFields = [
    { name: 'question', label: 'Savol', type: 'input' },
    { name: 'answer', label: 'Javob', type: 'textarea', rows: 5 },
]

const rows = ref([])
const meta = ref(null)
const currentPage = ref(1)
const route = useRoute()
const router = useRouter()
const form = reactive({ ...emptyLocalizedFields(['question', 'answer']), category: '', order: 0, is_active: true })
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

const fetchPage = async (page = 1) => {
    currentPage.value = page
    const res = await faqService.getAdminList({ page, per_page: 15 })
    rows.value = res.data ?? []
    meta.value = res.meta ?? null
}

const resetForm = () => {
    Object.assign(form, { ...emptyLocalizedFields(['question', 'answer']), category: '', order: 0, is_active: true })
}

const loadCurrent = async () => {
    if (!route.params.id) return
    const res = await faqService.getAdminList()
    const row = (res.data ?? []).find((item) => String(item.id) === String(route.params.id))
    if (!row) return
    assignLocalizedFromRow(form, row, ['question', 'answer'])
    Object.assign(form, { category: row.category || '', order: row.order || 0, is_active: Boolean(row.is_active) })
}

const save = async () => {
    const fields = ['question', 'answer']
    const missing = validateAdminLocalizedFields(form, fields)
    if (missing.length) {
        alert(`To‘ldiring: ${missing.join(', ')}`)
        return
    }

    const payload = buildAdminPayload(form, fields)

    if (isEditMode.value) await faqService.update(route.params.id, payload)
    else await faqService.create(payload)
    await router.push('/admin/faq')
}

const remove = async (id) => {
    if (!confirm('Savol o‘chirilsinmi?')) return
    await faqService.remove(id)
    await fetchPage(currentPage.value)
}

const move = async (row, direction) => {
    const sorted = [...rows.value].sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
    const idx = sorted.findIndex((item) => item.id === row.id)
    const target = idx + direction
    if (idx < 0 || target < 0 || target >= sorted.length) return
    const other = sorted[target]
    await faqService.update(row.id, { order: other.order ?? 0 })
    await faqService.update(other.id, { order: row.order ?? 0 })
    await fetchPage(currentPage.value)
}

const hydrate = async () => {
    if (isListMode.value) await fetchPage(1)
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
