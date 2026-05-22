<template>
    <AdminCrudShell :title="title" :create-to="createTo">

        <!-- BOLALAR RO'YXATI -->
        <template v-if="isCasesListMode">
            <ListState :loading="casesLoading" :error="casesError" :empty="cases.length === 0">
                <AdminTable :columns="caseColumns" :rows="cases">
                    <template #cell-photo_url="{ row }">
                        <img
                            v-if="row.photo_url"
                            :src="row.photo_url"
                            :alt="row.name"
                            class="h-12 w-12 rounded-lg object-cover"
                        />
                        <span v-else class="text-xs text-gray-400">—</span>
                    </template>

                    <template #cell-actions="{ row }">
                        <router-link
                            :to="`/admin/treatment-processes/${row.id}`"
                            class="inline-flex items-center rounded-lg bg-[#2A7DE1] px-3 py-2 text-sm text-white hover:bg-[#2569c7]"
                        >
                            Davolanish jarayoni
                        </router-link>
                    </template>
                </AdminTable>
            </ListState>

            <AdminPagination
                v-if="casesMeta && casesMeta.last_page > 1"
                :current-page="casesMeta.current_page || 1"
                :last-page="casesMeta.last_page || 1"
                :summary="`${casesMeta.total || 0} ta holat`"
                @change="loadCasesPage"
            />
        </template>

        <!-- BOLA UCHUN JARAYONLAR RO'YXATI -->
        <template v-else-if="isCaseProcessesMode">
            <div class="mb-4">
                <router-link
                    to="/admin/treatment-processes"
                    class="text-sm text-blue-600 hover:underline"
                >
                    ← Barcha holatlar
                </router-link>
                <p v-if="selectedCase" class="mt-2 text-lg font-semibold text-gray-900">
                    {{ selectedCase.name }}
                </p>
            </div>

            <ListState :loading="loading" :error="error" :empty="items.length === 0">
                <AdminTable :columns="columns" :rows="items">
                    <template #cell-image_count="{ row }">
                        {{ row.image_count ?? (row.images?.length || 0) }}
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-3">
                            <router-link
                                :to="`/admin/treatment-processes/${caseId}/edit/${row.id}`"
                                class="p-2 rounded-md hover:bg-amber-50 text-amber-600"
                                title="Tahrirlash"
                            >
                                <Pencil class="w-5 h-5" />
                            </router-link>
                            <button
                                type="button"
                                class="p-2 rounded-md hover:bg-red-50 text-red-600"
                                title="O‘chirish"
                                @click="remove(row.id)"
                            >
                                <Trash2 class="w-5 h-5" />
                            </button>
                        </div>
                    </template>
                </AdminTable>
            </ListState>

            <AdminPagination
                v-if="processesMeta && processesMeta.last_page > 1"
                :current-page="processesMeta.current_page || 1"
                :last-page="processesMeta.last_page || 1"
                :summary="`${processesMeta.total || 0} ta bosqich`"
                @change="loadProcessesPage"
            />
        </template>

        <!-- YARATISH / TAHRIRLASH -->
        <template v-else>
            <div class="mb-4">
                <router-link
                    :to="`/admin/treatment-processes/${caseId}`"
                    class="text-sm text-blue-600 hover:underline"
                >
                    ← {{ selectedCase?.name || 'Orqaga' }}
                </router-link>
            </div>

            <form class="bg-white p-6 rounded-xl shadow space-y-4" @submit.prevent="save">
                <LocalizedFieldTabs
                    v-model="form"
                    :fields="processLocalizedFields"
                />

                <input
                    v-model.number="form.sort_order"
                    type="number"
                    min="0"
                    class="input"
                    placeholder="Tartib raqami"
                />

                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600 mb-2 block">Rasmlar</label>

                    <input
                        type="file"
                        accept="image/*"
                        multiple
                        class="input flex-1"
                        @change="handleFilesChange"
                    />

                    <p v-if="uploading" class="text-sm text-gray-500 mt-2">Yuklanmoqda...</p>

                    <div v-if="form.images.length" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <div
                            v-for="(image, index) in form.images"
                            :key="`${image}-${index}`"
                            class="relative rounded-lg border overflow-hidden"
                        >
                            <img :src="image" alt="" class="h-28 w-full object-cover" />
                            <button
                                type="button"
                                class="absolute top-2 right-2 bg-white/90 text-red-600 rounded-full w-7 h-7 text-sm"
                                @click="removeImage(index)"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 flex gap-3 mt-2">
                    <button type="submit" class="btn-primary" :disabled="saving">
                        {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                    <router-link :to="`/admin/treatment-processes/${caseId}`" class="btn-secondary">
                        Bekor qilish
                    </router-link>
                </div>
            </form>
        </template>
    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Pencil, Trash2 } from 'lucide-vue-next'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import ListState from '@/components/shared/ListState.vue'
import caseService from '@/services/caseService'
import treatmentProcessService from '@/services/treatmentProcessService'
import mediaService from '@/services/mediaService'
import { useTreatmentProcesses } from '@/composables/useTreatmentProcesses'
import LocalizedFieldTabs from '@/admin/components/common/LocalizedFieldTabs.vue'
import { emptyLocalizedFields, assignLocalizedFromRow, validateAdminLocalizedFields, buildAdminPayload } from '@/utils/localizedContent'

const processLocalizedFields = [
    { name: 'title', label: 'Sarlavha', type: 'input' },
    { name: 'description', label: 'Tavsif', type: 'textarea', rows: 6 },
]

const route = useRoute()
const router = useRouter()
const { items, meta: processesMeta, loading, error, fetchItems, removeItem } = useTreatmentProcesses()

const cases = ref([])
const casesMeta = ref(null)
const casesLoading = ref(false)
const casesError = ref(null)
const selectedCase = ref(null)

const caseId = computed(() => route.params.caseId || null)
const processId = computed(() => route.params.processId || null)

const isCasesListMode = computed(() => route.name === 'admin-treatment-processes')
const isCaseProcessesMode = computed(() => route.name === 'admin-treatment-processes-case')
const isCreateMode = computed(() => route.name === 'admin-treatment-processes-create')
const isEditMode = computed(() => route.name === 'admin-treatment-processes-edit')

const title = computed(() => {
    if (isCasesListMode.value) return 'Davolanish jarayoni — Holatlar'
    if (isCaseProcessesMode.value) return selectedCase.value?.name || 'Davolanish jarayoni'
    if (isEditMode.value) return 'Davolanish bosqichini tahrirlash'
    return 'Yangi davolanish bosqichi'
})

const createTo = computed(() => {
    if (!isCaseProcessesMode.value || !caseId.value) return ''
    return `/admin/treatment-processes/${caseId.value}/create`
})

const caseColumns = [
    { key: 'name', label: 'Bemor ismi' },
    { key: 'location', label: 'Hudud' },
    { key: 'photo_url', label: 'Rasm' },
    { key: 'actions', label: 'Amallar' },
]

const columns = [
    { key: 'title', label: 'Sarlavha' },
    { key: 'image_count', label: 'Rasmlar soni' },
    { key: 'sort_order', label: 'Tartib' },
    { key: 'actions', label: 'Amallar' },
]

const form = reactive({
    ...emptyLocalizedFields(['title', 'description']),
    images: [],
    sort_order: 0,
})

const uploading = ref(false)
const saving = ref(false)

const loadCasesPage = async (page = 1) => {
    casesLoading.value = true
    casesError.value = null

    try {
        const res = await caseService.fetchList({ admin: true, page, per_page: 15 })
        cases.value = res.data ?? []
        casesMeta.value = res.meta ?? null
    } catch (err) {
        cases.value = []
        casesMeta.value = null
        casesError.value = err?.message || 'Holatlar ro‘yxatini yuklab bo‘lmadi'
    } finally {
        casesLoading.value = false
    }
}

const loadProcessesPage = (page = 1) => fetchItems({ case_id: caseId.value, page, per_page: 15 })

const loadSelectedCase = async () => {
    if (!caseId.value) {
        selectedCase.value = null
        return
    }

    const res = await caseService.getCaseById(caseId.value)
    selectedCase.value = res.data || null
}

const resetForm = () => {
    Object.assign(form, {
        ...emptyLocalizedFields(['title', 'description']),
        images: [],
        sort_order: 0,
    })
}

const loadCurrent = async () => {
    if (!processId.value) return

    const res = await treatmentProcessService.getById(processId.value)
    const row = res.data

    if (!row) return

    assignLocalizedFromRow(form, row, ['title', 'description'])
    Object.assign(form, {
        images: Array.isArray(row.images) ? [...row.images] : [],
        sort_order: Number(row.sort_order || 0),
    })
}

const handleFilesChange = async (event) => {
    const files = Array.from(event.target.files || [])
    if (!files.length) return

    uploading.value = true

    try {
        for (const file of files) {
            const { data, error } = await mediaService.upload(file, 'treatment_processes')

            if (error) {
                window.alert(`${file.name}: ${error.message}`)
                continue
            }

            if (data?.url) {
                form.images.push(data.url)
            }
        }
    } finally {
        uploading.value = false
        event.target.value = ''
    }
}

const removeImage = (index) => {
    form.images.splice(index, 1)
}

const save = async () => {
    if (!caseId.value) return

    const fields = ['title', 'description']
    const missing = validateAdminLocalizedFields(form, fields)
    if (missing.length) {
        alert(`To‘ldiring: ${missing.join(', ')}`)
        return
    }

    saving.value = true

    try {
        const payload = buildAdminPayload(form, fields, {
            case_id: Number(caseId.value),
            images: form.images,
        })

        if (isEditMode.value) {
            await treatmentProcessService.update(processId.value, payload)
        } else {
            await treatmentProcessService.create(payload)
        }

        await router.push(`/admin/treatment-processes/${caseId.value}`)
    } finally {
        saving.value = false
    }
}

const remove = async (id) => {
    if (!confirm('Davolanish jarayoni o‘chirilsinmi?')) return
    await removeItem(id, { case_id: caseId.value })
}

const hydrate = async () => {
    if (isCasesListMode.value) {
        await loadCasesPage()
        return
    }

    await loadSelectedCase()

    if (isCaseProcessesMode.value) {
        await loadProcessesPage()
        return
    }

    if (isEditMode.value) {
        await loadCurrent()
        return
    }

    if (isCreateMode.value) {
        resetForm()
    }
}

onMounted(hydrate)
watch(() => route.fullPath, hydrate)
</script>

<style scoped>
.input { border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 14px; width: 100%; }
.btn-primary { background: #2A7DE1; color: #fff; border-radius: 12px; padding: 10px 18px; }
.btn-secondary { border: 1px solid #ddd; border-radius: 12px; padding: 10px 18px; }
</style>
