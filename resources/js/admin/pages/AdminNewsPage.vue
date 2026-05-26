<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/news/create' : ''">
        <template v-if="isListMode">
            <AdminTable :columns="columns" :rows="rows">
                <template #cell-category="{ row }">
                    {{ categoryLabel(row.category) }}
                </template>
                <template #cell-published_at="{ row }">
                    {{ new Date(row.published_at || row.created_at).toLocaleString() }}
                </template>
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

            <AdminPagination
                v-if="meta"
                :current-page="meta.current_page || 1"
                :last-page="meta.last_page || 1"
                :summary="`${meta.total || 0} ta yangilik`"
                @change="fetchPage"
            />
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

                <p class="text-sm text-[#2A7DE1] font-medium mb-2">
                    {{ categoryLabel(form.category) }}
                </p>

                <h1 class="text-2xl font-bold mb-2">
                    {{ form.title }}
                </h1>

                <p class="text-gray-500 mb-4">
                    <span>
                        Yaratilgan: {{ new Date(form.created_at).toLocaleString() }}
                    </span>
                    <span v-if="form.published_at" class="ml-3">
                        Chop etilgan: {{ new Date(form.published_at).toLocaleString() }}
                    </span>
                    <span v-else class="ml-3 text-amber-600">
                        Qoralama
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
                        Tahrirlash
                    </router-link>
                </div>
            </div>
        </template>

        <template v-else>
            <form class="bg-white p-6 rounded-xl shadow space-y-4" @submit.prevent="save">
                <div>
                    <label class="field-label">Tur</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-2">
                        <label
                            v-for="option in categoryOptions"
                            :key="option.value"
                            class="type-option"
                            :class="{ 'type-option-active': form.category === option.value }"
                        >
                            <input
                                v-model="form.category"
                                type="radio"
                                :value="option.value"
                                class="sr-only"
                            />
                            <span class="font-medium text-gray-900">{{ option.label }}</span>
                        </label>
                    </div>
                </div>

                <LocalizedFieldTabs
                    v-model="form"
                    :fields="newsLocalizedFields"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Sahifa manzili (ixtiyoriy)</label>
                        <input v-model="form.slug" class="input" :placeholder="t('admin.placeholders.slug')" />
                        <p class="text-xs text-gray-500 mt-1">{{ t('admin.hints.slug') }}</p>
                    </div>
                    <div>
                        <label class="field-label">Holat</label>
                        <select v-model="form.status" class="input">
                            <option value="draft">Qoralama</option>
                            <option value="published">Chop etish</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="field-label">Muqova rasmi</label>
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

                <p v-if="saveError" class="text-sm text-red-600">{{ saveError }}</p>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary" :disabled="saving">
                        {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
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
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import LocalizedFieldTabs from '@/admin/components/common/LocalizedFieldTabs.vue'
import newsService from '@/services/newsService'
import mediaService from '@/services/mediaService'
import { emptyLocalizedFields, assignLocalizedFromRow, validateAdminLocalizedFields, buildAdminPayload } from '@/utils/localizedContent'
import { Pencil, Trash2, Eye } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const newsLocalizedFields = [
    { name: 'title', label: 'Sarlavha', type: 'input' },
    { name: 'excerpt', label: 'Qisqa tavsif', type: 'textarea', rows: 2 },
    { name: 'content', label: 'Matn', type: 'textarea', rows: 8 },
]

const rows = ref([])
const meta = ref(null)
const route = useRoute()
const router = useRouter()
const saving = ref(false)
const saveError = ref('')
const uploading = ref(false)

const categoryOptions = [
    { value: 'news', label: 'Yangilik' },
    { value: 'success_story', label: 'Muvaffaqiyat hikoyasi' },
    { value: 'announcement', label: "E'lon" },
]

const defaultForm = () => ({
    ...emptyLocalizedFields(['title', 'excerpt', 'content']),
    slug: '',
    cover_image: '',
    status: 'draft',
    category: 'news',
})

const form = reactive(defaultForm())

const isListMode = computed(() => route.name === 'admin-news')
const isEditMode = computed(() => route.name === 'admin-news-edit')
const isViewMode = computed(() => route.name === 'admin-news-view')

const title = computed(() => {
    if (isListMode.value) return 'Yangiliklar'
    if (isEditMode.value) return 'Yangilikni tahrirlash'
    if (isViewMode.value) return 'Yangilik'
    return 'Yangi yangilik yaratish'
})

const columns = [
    { key: 'title', label: 'Sarlavha' },
    { key: 'category', label: 'Tur' },
    { key: 'status', label: 'Holat' },
    { key: 'published_at', label: 'Chop etilgan sana' },
    { key: 'actions', label: 'Amallar' },
]

const categoryLabel = (value) => {
    return categoryOptions.find((item) => item.value === value)?.label || value || '—'
}

const load = async (page = 1) => {
    const res = await newsService.getAdminList({ page })
    rows.value = res.data ?? []
    meta.value = res.meta
}

const fetchPage = (page) => {
    load(page)
}

const closeMessage = () => {
    router.back()
}

const resetForm = () => {
    Object.assign(form, defaultForm())
    saveError.value = ''
}

const handleFileChange = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    uploading.value = true

    try {
        const { data, error } = await mediaService.upload(file, 'news')

        if (error) {
            window.alert(error.message)
            return
        }

        form.cover_image = data?.url ?? ''
    } finally {
        uploading.value = false
        e.target.value = ''
    }
}

const loadCurrent = async () => {
    if (!route.params.id) return

    const res = await newsService.getById(route.params.id)
    const row = res.data

    if (!row) return

    assignLocalizedFromRow(form, row, ['title', 'excerpt', 'content'])
    Object.assign(form, {
        slug: row.slug || '',
        cover_image: row.cover_image || '',
        status: row.status || 'draft',
        category: row.category || 'news',
        created_at: row.created_at,
        published_at: row.published_at,
    })
}

const save = async () => {
    saving.value = true
    saveError.value = ''

    const fields = ['title', 'excerpt', 'content']
    const missing = validateAdminLocalizedFields(form, fields)
    if (missing.length) {
        saveError.value = `To‘ldiring: ${missing.join(', ')}`
        saving.value = false
        return
    }

    const payload = buildAdminPayload(form, fields)

    try {
        if (isEditMode.value) {
            await newsService.update(route.params.id, payload)
        } else {
            await newsService.create(payload)
        }

        await router.push('/admin/news')
    } catch (error) {
        const message = error.response?.data?.message
        const errors = error.response?.data?.errors

        if (errors) {
            saveError.value = Object.values(errors).flat().join(' ')
        } else {
            saveError.value = message || 'Saqlashda xatolik yuz berdi'
        }
    } finally {
        saving.value = false
    }
}

const remove = async (id) => {
    if (!confirm('Yangilik o‘chirilsinmi?')) return
    await newsService.remove(id)
    await load(meta.value?.current_page || 1)
}

const hydrate = async () => {
    if (isListMode.value) {
        await load()
    } else if (isEditMode.value || isViewMode.value) {
        await loadCurrent()
    } else {
        resetForm()
    }
}

onMounted(hydrate)
watch(() => route.fullPath, hydrate)
</script>

<style scoped>
.field-label {
    display: block;
    font-size: 0.875rem;
    color: #4b5563;
    margin-bottom: 0.25rem;
}
.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
}
.type-option {
    display: block;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 14px;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
}
.type-option-active {
    border-color: #2a7de1;
    background: #eff6ff;
}
.btn-primary {
    background: #2a7de1;
    color: #fff;
    border-radius: 12px;
    padding: 10px 18px;
}
.btn-primary:disabled {
    opacity: 0.6;
}
.btn-secondary {
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 10px 18px;
}
</style>
