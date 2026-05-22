<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/cases/create' : ''">

        <p
            v-if="isListMode && route.query.success"
            class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700"
        >
            {{ route.query.success }}
        </p>

        <!-- ================= LIST ================= -->
        <template v-if="isListMode">
            <ListState :loading="loading" :error="error" :empty="cases.length === 0">

                <AdminTable :columns="columns" :rows="cases">

                    <template #cell-status="{ row }">
                        <StatusBadge :status="row.status" :map="CASE_STATUSES" />
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-3">

                            <!-- KO‘RISH -->
                            <router-link
                                :to="`/admin/cases/${row.id}`"
                                class="p-2 rounded-md hover:bg-blue-50 text-blue-600"
                                title="Ko‘rish"
                            >
                                <Eye class="w-5 h-5" />
                            </router-link>

                            <!-- EDIT -->
                            <router-link
                                :to="`/admin/cases/${row.id}/edit`"
                                class="p-2 rounded-md hover:bg-amber-50 text-amber-600"
                                title="Tahrirlash"
                            >
                                <Pencil class="w-5 h-5" />
                            </router-link>

                            <!-- DELETE -->
                            <button
                                @click="remove(row.id)"
                                class="p-2 rounded-md hover:bg-red-50 text-red-600"
                                title="O‘chirish"
                            >
                                <Trash2 class="w-5 h-5" />
                            </button>

                        </div>
                    </template>

                </AdminTable>

            </ListState>

            <AdminPagination
                v-if="meta && meta.last_page > 1"
                :current-page="meta.current_page || 1"
                :last-page="meta.last_page || 1"
                :summary="`${meta.total || 0} ta holat`"
                @change="fetchPage"
            />
        </template>

        <!-- ================= VIEW ================= -->
        <template v-else-if="isViewMode && current">
            <div class="grid lg:grid-cols-3 gap-6">

                <!-- LEFT: MAIN INFO -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- HERO CARD -->
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

                        <img
                            v-if="current.photo_url"
                            :src="current.photo_url"
                            class="w-full h-64 object-cover"
                        />

                        <div class="p-5">

                            <div class="flex items-start justify-between">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">
                                        {{ current.name }}
                                    </h1>

                                    <p class="text-sm text-gray-500 mt-1">
                                        📍 {{ current.location ?? '-' }}
                                    </p>
                                </div>

                                <StatusBadge
                                    :status="current.status"
                                    :map="CASE_STATUSES"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-5 text-sm">

                                <div class="info-box">
                                    <span>Yosh</span>
                                    <b>{{ current.age ?? '-' }}</b>
                                </div>

                                <div class="info-box">
                                    <span>Kasallik</span>
                                    <b>{{ current.condition ?? '-' }}</b>
                                </div>

                                <div class="info-box">
                                    <span>Maqsad</span>
                                    <b>{{ current.goal_amount ?? 0 }}</b>
                                </div>

                                <div class="info-box">
                                    <span>Yig‘ilgan</span>
                                    <b>{{ current.raised_amount ?? 0 }}</b>
                                </div>

                            </div>

                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="bg-white rounded-2xl shadow-sm p-5">
                        <h3 class="font-semibold mb-3">📄 Tavsif</h3>
                        <p class="text-gray-600 leading-relaxed whitespace-pre-wrap">
                            {{ current.short_description ?? '-' }}
                        </p>
                    </div>

                    <!-- DOCUMENTS -->
                    <div class="bg-white rounded-2xl shadow-sm p-5" v-if="current.medical_documents?.length">
                        <h3 class="font-semibold mb-3">📎 Hujjatlar</h3>

                        <div class="space-y-2">
                            <a
                                v-for="(doc,i) in current.medical_documents"
                                :key="i"
                                :href="doc.url"
                                target="_blank"
                                class="flex items-center justify-between p-3 rounded-xl border hover:bg-gray-50 transition"
                            >
                        <span class="text-sm text-gray-700">
                            {{ doc.name }}
                        </span>
                                <span class="text-blue-500 text-sm">⬇</span>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- RIGHT: INFO SIDEBAR -->
                <div class="space-y-4">

                    <div class="bg-white rounded-2xl shadow-sm p-5">
                        <h3 class="font-semibold mb-4">📊 Qisqa ma’lumot</h3>

                        <div class="space-y-3 text-sm">

                            <div class="side-row">
                                <span>ID</span>
                                <b>#{{ current.id }}</b>
                            </div>

                            <div class="side-row">
                                <span>Status</span>
                                <StatusBadge :status="current.status" :map="CASE_STATUSES" />
                            </div>

                            <div class="side-row">
                                <span>Yosh</span>
                                <b>{{ current.age ?? '-' }}</b>
                            </div>

                            <div class="side-row">
                                <span>Kasallik</span>
                                <b class="text-right max-w-[60%]">
                                    {{ current.condition ?? '-' }}
                                </b>
                            </div>

                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-5">
                        <h3 class="font-semibold mb-3">💰 Moliyaviy</h3>

                        <div class="space-y-3 text-sm">

                            <div class="side-row">
                                <span>Maqsad</span>
                                <b>{{ current.goal_amount ?? 0 }}</b>
                            </div>

                            <div class="side-row">
                                <span>Yig‘ilgan</span>
                                <b class="text-green-600">
                                    {{ current.raised_amount ?? 0 }}
                                </b>
                            </div>

                            <div class="side-row">
                                <span>Qoldi</span>
                                <b class="text-orange-500">
                                    {{ (current.goal_amount ?? 0) - (current.raised_amount ?? 0) }}
                                </b>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </template>

        <!-- ================= FORM ================= -->
        <template v-else>
            <form @submit.prevent="save" class="space-y-6">

                <!-- 🔹 1. ASOSIY MA'LUMOT -->
                <div class="card">
                    <h2 class="text-lg font-semibold mb-4">Asosiy ma'lumotlar</h2>

                    <div class="mb-4">
                        <label class="label">Yosh</label>
                        <input v-model.number="form.age" type="number" class="input" placeholder="Masalan: 12" />
                    </div>

                    <LocalizedFieldTabs
                        v-model="form"
                        :fields="caseLocalizedFields"
                    />
                </div>

                <!-- 🔹 2. MOLIYAVIY -->
                <div class="card">
                    <h2 class="text-lg font-semibold mb-4">Mablag‘</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Kerak summa</label>
                            <input v-model.number="form.goal_amount" type="number" class="input" />
                        </div>

                        <div>
                            <label class="label">Yig‘ilgan summa</label>
                            <input v-model.number="form.raised_amount" type="number" class="input" />
                        </div>
                    </div>
                </div>

                <!-- 🔹 3. STATUS -->
                <div class="card">
                    <h2 class="text-lg font-semibold mb-4">Holati</h2>

                    <div class="grid md:grid-cols-3 gap-4">

                        <div>
                            <label class="label">Kategoriya</label>
                            <select v-model="form.category" class="input">
                                <option
                                    v-for="c in CASE_CATEGORIES"
                                    :key="c.value"
                                    :value="c.value"
                                >
                                    {{ c.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="label">Shoshilinchlik</label>
                            <select v-model="form.urgency" class="input">
                                <option
                                    v-for="u in CASE_URGENCY"
                                    :key="u.value"
                                    :value="u.value"
                                >
                                    {{ u.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="label">Holati</label>
                            <select v-model="form.status" class="input">
                                <option value="new">Yangi</option>
                                <option value="active">Faol</option>
                                <option value="paused">To‘xtatilgan</option>
                                <option value="completed">Yakunlangan</option>
                            </select>
                        </div>

                    </div>

                    <div class="flex gap-6 mt-4">
                        <label class="checkbox">
                            <input type="checkbox" v-model="form.is_featured" />
                            Asosiy holat
                        </label>

                        <label class="checkbox">
                            <input type="checkbox" v-model="form.is_urgent" />
                            Shoshilinch
                        </label>
                    </div>
                </div>

                <!-- 🔹 4. RASM -->
                <div class="card">
                    <h2 class="text-lg font-semibold mb-4">Rasm</h2>

                    <div class="upload-box" @click="$refs.imageInput.click()">
                        <input ref="imageInput" type="file" class="hidden" @change="uploadImage" />

                        <div v-if="!previewUrl">
                            📷 Rasm yuklash uchun bosing
                        </div>

                        <img v-else :src="previewUrl" class="preview-img" />
                    </div>
                </div>

                <!-- 🔹 5. HUJJATLAR -->
                <div class="card">
                    <h2 class="text-lg font-semibold mb-4">Hujjatlar</h2>

                    <div class="upload-box" @click="$refs.docsInput.click()">
                        <input ref="docsInput" type="file" multiple class="hidden" @change="uploadDocs" />
                        📎 Hujjat yuklash
                    </div>

                    <div v-if="form.medical_documents.length" class="mt-4 space-y-2">
                        <div v-for="(doc, i) in form.medical_documents" :key="i" class="doc-item">
                            <span>{{ doc.name }}</span>
                            <button type="button" @click="removeDoc(i)">❌</button>
                        </div>
                    </div>
                </div>

                <!-- 🔹 BUTTON -->
                <div class="flex justify-end gap-3">
                    <router-link to="/admin/cases" class="btn-secondary">Bekor qilish</router-link>
                    <button class="btn-primary">Saqlash</button>
                </div>

            </form>
        </template>

    </AdminCrudShell>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCases } from '@/composables/useCases'
import caseService from '@/services/caseService'
import mediaService from '@/services/mediaService'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'

import { CASE_STATUSES } from '@/constants/statuses'
import { CASE_CATEGORIES, CASE_URGENCY } from '@/constants/cases'
import { watch } from 'vue'
import { Eye, Pencil, Trash2 } from 'lucide-vue-next'
import LocalizedFieldTabs from '@/admin/components/common/LocalizedFieldTabs.vue'
import { emptyLocalizedFields, assignLocalizedFromRow, validateAdminLocalizedFields, buildAdminPayload } from '@/utils/localizedContent'

const caseLocalizedFields = [
    { name: 'name', label: 'Ism', type: 'input' },
    { name: 'location', label: 'Joylashuv', type: 'input' },
    { name: 'condition', label: 'Kasallik / holat', type: 'input' },
    { name: 'short_description', label: 'Qisqa tavsif', type: 'textarea', rows: 4 },
]

const route = useRoute()
const router = useRouter()

const { cases, meta, loading, error, fetchCases, createCase, updateCase, deleteCase } = useCases()

const fetchPage = (page = 1) => fetchCases({ admin: true, page, per_page: 15 })

const current = ref(null)
const previewUrl = ref('')

const isListMode = computed(() => route.name === 'admin-cases')
const isEditMode = computed(() => route.name === 'admin-cases-edit')
const isViewMode = computed(() => route.name === 'admin-cases-view')

const title = computed(() => isListMode.value ? 'Cases' : isEditMode.value ? 'Tahrirlash' : 'Ko‘rish')

const form = reactive({
    ...emptyLocalizedFields(['name', 'location', 'condition', 'short_description']),
    age: null,
    goal_amount: 0,
    raised_amount: 0,
    urgency: 'medium',
    category: 'illness',
    status: 'active',
    is_featured: false,
    is_urgent: false,

    // 🖼 1 TA RASM
    photo_url: '',

    // 📎 MULTI HUJJAT
    medical_documents: []
})

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Ism' },
    { key: 'status', label: 'Holati' },
    { key: 'goal_amount', label: 'Maqsad' },
    { key: 'actions', label: 'Harakatlar' },
]

const loadCurrent = async () => {
    const id = route.params.id

    if (!id || id === 'undefined') return

    const res = await caseService.getCaseById(id)
    current.value = res.data

    if (isEditMode.value && res.data) {
        assignLocalizedFromRow(form, res.data, ['name', 'location', 'condition', 'short_description'])
        Object.assign(form, {
            age: res.data.age ?? null,
            goal_amount: Number(res.data.goal_amount ?? 0),
            raised_amount: Number(res.data.raised_amount ?? 0),
            urgency: res.data.urgency ?? 'o\'rta',
            category: res.data.category ?? 'kasallik',
            status: res.data.status ?? 'faol',
            is_featured: Boolean(res.data.is_featured),
            is_urgent: Boolean(res.data.is_urgent),
            photo_url: res.data.photo_url ?? '',
            medical_documents: res.data.medical_documents ?? [],
        })

        previewUrl.value = form.photo_url
    }
}

const uploadImage = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    previewUrl.value = URL.createObjectURL(file)

    const { data, error } = await mediaService.upload(file, 'cases/images')

    if (error) {
        window.alert(error.message)
        previewUrl.value = form.photo_url || ''
        return
    }

    if (data?.url) {
        form.photo_url = data.url
        previewUrl.value = data.url
    }
}

const uploadDocs = async (e) => {
    const files = Array.from(e.target.files || [])

    for (const file of files) {
        const { data, error } = await mediaService.upload(file, 'cases/docs')

        if (error) {
            window.alert(`${file.name}: ${error.message}`)
            continue
        }

        if (data?.url) {
            form.medical_documents.push({
                url: data.url,
                name: file.name,
            })
        }
    }
}
watch(
    () => route.fullPath,
    async () => {
        if (isListMode.value) {
            await fetchPage()
        } else {
            await loadCurrent()
        }
    },
    { immediate: true }
)

const removeDoc = (i) => {
    form.medical_documents.splice(i, 1)
}

const save = async () => {
    const fields = ['name', 'location', 'condition', 'short_description']
    const missing = validateAdminLocalizedFields(form, fields)
    if (missing.length) {
        alert(`To‘ldiring: ${missing.join(', ')}`)
        return
    }

    const payload = buildAdminPayload(form, fields, {
        medical_documents: form.medical_documents,
    })

    const res = isEditMode.value
        ? await updateCase(route.params.id, payload)
        : await createCase(payload)

    if (res?.error) return

    router.push('/admin/cases')
}

const remove = async (id) => {
    if (!confirm('O‘chirishni tasdiqlaysizmi?')) return
    await deleteCase(id)
    await fetchPage(meta.value?.current_page || 1)
}

onMounted(async () => {
    if (isListMode.value) await fetchCases({ admin: true })
    else await loadCurrent()
})
</script>

<style scoped>
.input {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 14px;
    transition: 0.2s;
}

.input:focus {
    outline: none;
    border-color: #2A7DE1;
    box-shadow: 0 0 0 2px rgba(42,125,225,0.2);
}

.btn-primary {
    background: linear-gradient(135deg, #2A7DE1, #1d5fbf);
    color: white;
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: 500;
}

.btn-secondary {
    border: 1px solid #ddd;
    padding: 10px 18px;
    border-radius: 12px;
}
.card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.label {
    display: block;
    font-size: 13px;
    margin-bottom: 6px;
    color: #555;
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 6px;
}

.upload-box {
    border: 2px dashed #ccc;
    padding: 20px;
    text-align: center;
    border-radius: 12px;
    cursor: pointer;
}

.preview-img {
    height: 120px;
    margin: auto;
    border-radius: 10px;
}

.doc-item {
    display: flex;
    justify-content: space-between;
    background: #f9fafb;
    padding: 10px;
    border-radius: 8px;
}
.info-box {
    background: #f9fafb;
    padding: 10px;
    border-radius: 12px;
}

.info-box span {
    display: block;
    font-size: 11px;
    color: #6b7280;
}

.info-box b {
    font-size: 14px;
    color: #111827;
}

.side-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px dashed #eee;
}

.side-row:last-child {
    border-bottom: none;
}
</style>
