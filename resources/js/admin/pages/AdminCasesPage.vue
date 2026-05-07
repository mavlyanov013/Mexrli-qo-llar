<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/cases/create' : ''">

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
        </template>

        <!-- ================= VIEW ================= -->
        <template v-else-if="isViewMode && current">
            <div class="space-y-4 bg-white p-5 rounded-xl shadow">

                <img v-if="current.photo_url"
                     :src="current.photo_url"
                     class="w-48 h-48 object-cover rounded-xl" />

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <p><b>Ism:</b> {{ current.name }}</p>
                    <p><b>Yosh:</b> {{ current.age }}</p>
                    <p><b>Joylashuv:</b> {{ current.location }}</p>
                    <p><b>Holati:</b> {{ current.condition }}</p>
                    <p><b>Maqsad:</b> {{ current.goal_amount }}</p>
                    <p><b>Yig‘ilgan:</b> {{ current.raised_amount }}</p>
                    <p><b>Status:</b> <StatusBadge :status="current.status" :map="CASE_STATUSES" /></p>
                </div>

                <p class="text-gray-600">{{ current.short_description }}</p>

                <!-- HUJJATLAR -->
                <div v-if="current.medical_documents?.length">
                    <h3 class="font-semibold mt-4">Hujjatlar:</h3>
                    <ul class="list-disc ml-5 text-blue-600">
                        <li v-for="(doc,i) in current.medical_documents" :key="i">
                            <a :href="doc" target="_blank">Hujjat {{ i+1 }}</a>
                        </li>
                    </ul>
                </div>

            </div>
        </template>

        <!-- ================= FORM ================= -->
        <template v-else>
            <form class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-2 gap-4"
                  @submit.prevent="save">

                <!-- ISM -->
                <input v-model="form.name" class="input" placeholder="Ism" required />

                <!-- YOSH -->
                <input v-model.number="form.age" type="number" class="input" placeholder="Yosh" />

                <!-- JOY -->
                <input v-model="form.location" class="input" placeholder="Joylashuv" />

                <!-- HOLAT -->
                <input v-model="form.condition" class="input" placeholder="Kasallik / holat" />

                <!-- CATEGORY -->
                <select v-model="form.category" class="input">
                    <option v-for="c in CASE_CATEGORIES" :key="c" :value="c">{{ c }}</option>
                </select>

                <!-- URGENCY -->
                <select v-model="form.urgency" class="input">
                    <option v-for="u in CASE_URGENCY" :key="u" :value="u">{{ u }}</option>
                </select>

                <!-- STATUS -->
                <select v-model="form.status" class="input">
                    <option value="active">Faol</option>
                    <option value="closed">Yopilgan</option>
                </select>

                <!-- CHECKBOX -->
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.is_featured" />
                    Asosiy (featured)
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.is_urgent" />
                    Shoshilinch
                </label>

                <!-- PUL -->
                <input v-model.number="form.goal_amount" type="number" class="input" placeholder="Kerak summa" />
                <input v-model.number="form.raised_amount" type="number" class="input" placeholder="Yig‘ilgan summa" />

                <!-- RASM -->
                <input type="file" class="input md:col-span-2" @change="uploadImage" />

                <img v-if="previewUrl" :src="previewUrl" class="h-24 w-24 rounded-xl object-cover"  alt=""/>

                <!-- HUJJAT UPLOAD -->
                <input type="file"
                       multiple
                       class="input md:col-span-2"
                       @change="uploadDocs" />

                <div v-if="form.medical_documents.length" class="md:col-span-2">

                    <h3 class="font-semibold mb-2">Hujjatlar</h3>

                    <div class="space-y-2">
                        <div v-for="(doc, i) in form.medical_documents"
                             :key="i"
                             class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">

                            <a :href="doc.url"
                               target="_blank"
                               class="text-blue-600 text-sm">
                                📎 {{ doc.name || 'Hujjat ' + (i+1) }}
                            </a>

                            <button type="button"
                                    class="text-red-500 text-sm"
                                    @click="removeDoc(i)">
                                O‘chirish
                            </button>

                        </div>
                    </div>

                </div>

                <!-- DESCRIPTION -->
                <textarea v-model="form.short_description"
                          class="input md:col-span-2"
                          placeholder="Qisqa tavsif"></textarea>

                <!-- BUTTON -->
                <div class="md:col-span-2 flex gap-3 mt-3">
                    <button class="btn-primary">Saqlash</button>
                    <router-link to="/admin/cases" class="btn-secondary">Bekor qilish</router-link>
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
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'

import { CASE_STATUSES } from '@/constants/statuses'
import { CASE_CATEGORIES, CASE_URGENCY } from '@/constants/cases'
import { watch } from 'vue'
import { Eye, Pencil, Trash2 } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

const { cases, loading, error, fetchCases, createCase, updateCase, deleteCase } = useCases()

const current = ref(null)
const previewUrl = ref('')

const isListMode = computed(() => route.name === 'admin-cases')
const isEditMode = computed(() => route.name === 'admin-cases-edit')
const isViewMode = computed(() => route.name === 'admin-cases-view')

const title = computed(() => isListMode.value ? 'Cases' : isEditMode.value ? 'Tahrirlash' : 'Ko‘rish')

const form = reactive({
    name: '',
    age: null,
    location: '',
    condition: '',
    short_description: '',
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
    { key: 'status', label: 'Status' },
    { key: 'goal_amount', label: 'Maqsad' },
    { key: 'actions', label: 'Actions' },
]

const loadCurrent = async () => {
    const id = route.params.id

    if (!id || id === 'undefined') return

    const res = await caseService.getCaseById(id)
    current.value = res.data

    if (isEditMode.value && res.data) {
        Object.assign(form, {
            name: res.data.name ?? '',
            age: res.data.age ?? null,
            location: res.data.location ?? '',
            condition: res.data.condition ?? '',
            short_description: res.data.short_description ?? '',
            goal_amount: Number(res.data.goal_amount ?? 0),
            raised_amount: Number(res.data.raised_amount ?? 0),
            urgency: res.data.urgency ?? 'medium',
            category: res.data.category ?? 'illness',
            status: res.data.status ?? 'active',
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

    const res = await mediaService.upload(file, 'cases')

    if (!res.error) {
        form.photo_url = res.data.url
        previewUrl.value = res.data.url
    }
}

const uploadDocs = async (e) => {
    const files = Array.from(e.target.files || [])

    for (const file of files) {
        const res = await mediaService.upload(file, 'cases/docs')

        if (!res.error && res.data?.url) {
            form.medical_documents.push({
                url: res.data.url,
                name: file.name
            })
        }
    }
}
watch(
    () => route.fullPath,
    async () => {
        if (isListMode.value) {
            await fetchCases({ admin: true })
        } else {
            await loadCurrent()
        }
    },
    { immediate: true }
)

const removeDoc = (i) => form.medical_documents.splice(i, 1)

const save = async () => {

    const payload = {
        ...form,
        medical_documents: form.medical_documents
    }

    const result = isEditMode.value
        ? await updateCase(route.params.id, payload)
        : await createCase(payload)

    if (!result.error) {
        router.push('/admin/cases')
    }
}

const remove = async (id) => {
    if (!confirm('O‘chirishni tasdiqlaysizmi?')) return
    await deleteCase(id)
    fetchCases({ admin: true })
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
</style>
