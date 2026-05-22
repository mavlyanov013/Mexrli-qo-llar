<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/partners/create' : ''">
        <template v-if="isListMode">
        <ListState :loading="loading" :error="error" :empty="partners.length === 0">
            <AdminTable :columns="columns" :rows="partners">
                <template #cell-type="{ row }">
                    {{ getTypeLabel(row.type) }}
                </template>
                <template #cell-status="{ row }">
                    {{ row.is_active ? 'Faol' : 'Faol emas' }}
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex items-center gap-3">

                        <!-- VIEW -->
                        <router-link
                            :to="`/admin/partners/${row.id}`"
                            class="text-blue-600 hover:scale-110 transition"
                        >
                            <Eye class="w-4 h-4" />
                        </router-link>

                        <!-- EDIT -->
                        <router-link
                            :to="`/admin/partners/${row.id}/edit`"
                            class="text-amber-600 hover:scale-110 transition"
                        >
                            <Pencil class="w-4 h-4" />
                        </router-link>

                        <!-- DELETE -->
                        <button
                            @click="remove(row.id)"
                            class="text-red-600 hover:scale-110 transition"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>

                    </div>
                </template>
            </AdminTable>
        </ListState>

            <AdminPagination
                v-if="meta && meta.last_page > 1"
                :current-page="meta.current_page || 1"
                :last-page="meta.last_page || 1"
                :summary="`${meta.total || 0} ta hamkor`"
                @change="fetchPartnersPage"
            />
        </template>

        <template v-else-if="isViewMode && current">
            <div class="space-y-2 text-sm">
                <img v-if="current.logo_url" :src="current.logo_url" class="h-24 w-24 rounded-lg object-cover" />
                <p><strong>{{ t('admin.name') }}:</strong> {{ current.name }}</p>
                <p><strong>{{ t('admin.type') }}:</strong> {{ getTypeLabel(current?.type) }}</p>
                <p>
                    <strong>Status:</strong>
                    {{ getStatusLabel(current?.is_active) }}
                </p>
                <p>{{ current.description || '-' }}</p>
            </div>
        </template>

        <template v-else>
            <form
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6"
                @submit.prevent="save"
            >
                <LocalizedFieldTabs
                    v-model="draft"
                    :fields="[
                        { name: 'name', label: 'Hamkor nomi', type: 'input' },
                        { name: 'description', label: 'Tavsif', type: 'textarea', rows: 3 },
                    ]"
                />

                <!-- WEBSITE -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">
                        Web sayt (ixtiyoriy)
                    </label>
                    <input
                        v-model="draft.website"
                        type="text"
                        placeholder="https://example.uz"
                        class="w-full h-11 rounded-xl border border-gray-200 px-4 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition"
                    />
                </div>

                <!-- TYPE -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">
                        Hamkor turi
                    </label>
                    <select
                        v-model="draft.type"
                        class="w-full h-11 rounded-xl border border-gray-200 px-4 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition"
                    >
                        <option value="foundation">🏦 Fond</option>
                        <option value="ngo">🤝 NNT</option>
                        <option value="government">🏛 Davlat</option>
                        <option value="medical">🏥 Tibbiy</option>
                        <option value="media">📺 Media</option>
                        <option value="corporate">🏢 Korporativ</option>
                    </select>
                </div>

                <!-- STATUS -->
                <div class="flex items-center gap-3">
                    <input
                        v-model="draft.is_active"
                        type="checkbox"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded"
                    />
                    <span class="text-sm text-gray-700">
            Faol holatda bo‘lsin
        </span>
                </div>

                <!-- LOGO UPLOAD -->
                <!-- 🔹 LOGO -->
                <div class="card md:col-span-2">
                    <h2 class="text-lg font-semibold mb-4">Logotip</h2>

                    <div class="upload-box" @click="$refs.logoInput.click()">
                        <input
                            ref="logoInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="uploadLogo"
                        />

                        <div v-if="!previewUrl">
                            🖼 Logotip yuklash uchun bosing
                        </div>

                        <img v-else :src="previewUrl" class="preview-img" />
                    </div>

                    <button
                        v-if="previewUrl"
                        type="button"
                        class="mt-2 text-red-600 text-sm"
                        @click="removeLogo"
                    >
                        Logotipni o‘chirish
                    </button>
                </div>

                <!-- ACTIONS -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <router-link
                        to="/admin/partners"
                        class="px-5 py-2 rounded-xl border border-gray-300 text-sm hover:bg-gray-50"
                    >
                        Bekor qilish
                    </router-link>

                    <button
                        type="submit"
                        class="px-6 py-2 rounded-xl bg-[#2A7DE1] text-white text-sm font-medium hover:bg-blue-700 transition"
                    >
                        Saqlash
                    </button>
                </div>
            </form>
        </template>
    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { usePartners } from '@/composables/usePartners'
import { PARTNER_TYPES } from '@/constants/partners'
import { PAYMENT_STATUSES } from '@/constants/statuses'
import partnerService from '@/services/partnerService'
import mediaService from '@/services/mediaService'
import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { watch } from 'vue'
import { Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { ACTIVE_STATUSES } from '@/constants/statuses'
import { PARTNER_TYPE_LABELS } from '@/constants/partners'
import LocalizedFieldTabs from '@/admin/components/common/LocalizedFieldTabs.vue'
import { emptyLocalizedFields, assignLocalizedFromRow, validateAdminLocalizedFields, buildAdminPayload } from '@/utils/localizedContent'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const { partners, meta, loading, error, fetchPartners, createPartner, updatePartner, deletePartner, togglePartnerStatus } = usePartners()

const fetchPartnersPage = (page = 1) => fetchPartners({ admin: true, include_inactive: true, page, per_page: 15 })
const statusMap = {
    success: 'Faol',
    pending: 'Faol emas'
}
const current = ref(null)
const previewUrl = ref('')
const isListMode = computed(() => route.name === 'admin-partners')
const isEditMode = computed(() => route.name === 'admin-partners-edit')
const isViewMode = computed(() => route.name === 'admin-partners-view')
const title = computed(() => isListMode.value ? t('admin.partners') : isEditMode.value ? 'Edit partner' : route.name === 'admin-partners-create' ? 'Create partner' : 'Partner details')
const getTypeLabel = (val) => PARTNER_TYPE_LABELS[val] || val

const columns = [
    { key: 'name', label: t('admin.name') },
    { key: 'type', label: t('admin.type') },
    { key: 'status', label: t('admin.status') },
    { key: 'actions', label: t('admin.actions') },
]

const draft = reactive({
    ...emptyLocalizedFields(['name', 'description']),
    type: PARTNER_TYPES[0],
    logo_url: '',
    website: '',
    is_active: true,
})
const getStatusLabel = (val) => val ? 'Faol' : 'Faol emas'

const loadCurrent = async () => {
    const id = route.params.id

    if (!id || id === 'create') return  // 🔥 MUHIM

    const result = await partnerService.getById(id)
    current.value = result.data

    if (isEditMode.value && result.data) {
        assignLocalizedFromRow(draft, result.data, ['name', 'description'])
        draft.logo_url = result.data.logo_url || ''
        draft.website = result.data.website || ''
        draft.type = result.data.type || PARTNER_TYPES[0]
        draft.is_active = result.data.is_active !== false
        previewUrl.value = draft.logo_url
    }
}

const save = async () => {
    const fields = ['name', 'description']
    const missing = validateAdminLocalizedFields(draft, fields)
    if (missing.length) {
        alert(`To‘ldiring: ${missing.join(', ')}`)
        return
    }

    const payload = buildAdminPayload(draft, fields)

    const result = isEditMode.value
        ? await updatePartner(route.params.id, payload)
        : await createPartner(payload)
    if (!result.error) {
        router.push('/admin/partners')
    }
}

const uploadLogo = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    previewUrl.value = URL.createObjectURL(file)

    const { data, error } = await mediaService.upload(file, 'partners')

    if (error) {
        window.alert(error.message)
        previewUrl.value = draft.logo_url || ''
        return
    }

    if (data?.url) {
        draft.logo_url = data.url
        previewUrl.value = data.url
    }
}

const removeLogo = async () => {
    draft.logo_url = ''
    previewUrl.value = ''
}

const toggle = async (row) => {
    await togglePartnerStatus(row.id, !row.is_active)
    await fetchPartnersPage(meta.value?.current_page || 1)
}

const remove = async (id) => {
    if (!window.confirm('Bu hamkorni oʻchirib tashlang?')) return
    await deletePartner(id)
    await fetchPartnersPage(meta.value?.current_page || 1)
}

onMounted(async () => {
    if (isListMode.value) {
        await fetchPartnersPage(1)
    } else if (isEditMode.value || isViewMode.value) {
        await loadCurrent()
    }
})
watch(
    () => route.fullPath,
    async () => {
        if (isListMode.value) {
            await fetchPartnersPage(1)
        } else if (isEditMode.value || isViewMode.value) {
            await loadCurrent()
        }
    },
    { immediate: true }
)
</script>
<style scoped>
.upload-box {
    border: 2px dashed #d1d5db;
    padding: 20px;
    text-align: center;
    border-radius: 12px;
    cursor: pointer;
    transition: 0.2s;
}

.upload-box:hover {
    border-color: #2A7DE1;
    background: #f9fbff;
}

.preview-img {
    height: 120px;
    margin: auto;
    border-radius: 10px;
    object-fit: cover;
}
</style>
