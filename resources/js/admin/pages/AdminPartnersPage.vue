<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/partners/create' : ''">
        <template v-if="isListMode">
        <ListState :loading="loading" :error="error" :empty="partners.length === 0">
            <AdminTable :columns="columns" :rows="partners">
                <template #cell-status="{ row }">
                    <StatusBadge :status="row.is_active ? 'success' : 'pending'" :map="statusMap" />
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
        </template>

        <template v-else-if="isViewMode && current">
            <div class="space-y-2 text-sm">
                <img v-if="current.logo_url" :src="current.logo_url" class="h-24 w-24 rounded-lg object-cover" />
                <p><strong>{{ t('admin.name') }}:</strong> {{ current.name }}</p>
                <p><strong>{{ t('admin.type') }}:</strong> {{ current.type }}</p>
                <p><strong>{{ t('admin.status') }}:</strong>
                    <StatusBadge
                        :status="row?.is_active ? 'success' : 'pending'"
                        :map="statusMap"
                    />
                </p>
                <p>{{ current.description || '-' }}</p>
            </div>
        </template>

        <template v-else>
            <form class="grid grid-cols-1 gap-3 md:grid-cols-2" @submit.prevent="save">
                <input v-model="draft.name" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" :placeholder="t('admin.name')" required />
                <input v-model="draft.website" class="h-10 rounded-lg border border-gray-200 px-3 text-sm" placeholder="https://..." />
                <select v-model="draft.type" class="h-10 rounded-lg border border-gray-200 px-3 text-sm">
                    <option v-for="type in PARTNER_TYPES" :key="type" :value="type">{{ type }}</option>
                </select>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="draft.is_active" type="checkbox" />
                    Active
                </label>
                <input type="file" accept="image/*" class="h-10 rounded-lg border border-gray-200 px-3 text-sm md:col-span-2" @change="uploadLogo" />
                <div v-if="previewUrl" class="md:col-span-2 flex items-center gap-3">
                    <img :src="previewUrl" class="h-20 w-20 rounded-lg object-cover" />
                    <button type="button" class="text-red-600 text-sm" @click="removeLogo">Delete logo</button>
                </div>
                <textarea v-model="draft.description" class="rounded-lg border border-gray-200 px-3 py-2 text-sm md:col-span-2" rows="2" placeholder="description" />
                <div class="md:col-span-2 flex gap-2">
                    <button class="rounded-lg bg-[#2A7DE1] px-4 py-2 text-sm text-white" type="submit">{{ t('admin.save') }}</button>
                    <router-link to="/admin/partners" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">{{ t('admin.cancel') }}</router-link>
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
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { watch } from 'vue'
import { Eye, Pencil, Trash2 } from 'lucide-vue-next'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const { partners, loading, error, fetchPartners, createPartner, updatePartner, deletePartner, togglePartnerStatus } = usePartners()
const statusMap = PAYMENT_STATUSES
const current = ref(null)
const previewUrl = ref('')
const isListMode = computed(() => route.name === 'admin-partners')
const isEditMode = computed(() => route.name === 'admin-partners-edit')
const isViewMode = computed(() => route.name === 'admin-partners-view')
const title = computed(() => isListMode.value ? t('admin.partners') : isEditMode.value ? 'Edit partner' : route.name === 'admin-partners-create' ? 'Create partner' : 'Partner details')

const columns = [
    { key: 'name', label: t('admin.name') },
    { key: 'type', label: t('admin.type') },
    { key: 'status', label: t('admin.status') },
    { key: 'actions', label: t('admin.actions') },
]

const draft = reactive({
    name: '',
    type: PARTNER_TYPES[0],
    logo_url: '',
    website: '',
    description: '',
    is_active: true,
})

const loadCurrent = async () => {
    const result = await partnerService.getById(route.params.id)
    current.value = result.data
    if (isEditMode.value && result.data) {
        draft.name = result.data.name || ''
        draft.logo_url = result.data.logo_url || ''
        draft.website = result.data.website || ''
        draft.description = result.data.description || ''
        draft.type = result.data.type || PARTNER_TYPES[0]
        draft.is_active = result.data.is_active !== false
        previewUrl.value = draft.logo_url
    }
}

const save = async () => {
    const result = isEditMode.value
        ? await updatePartner(route.params.id, draft)
        : await createPartner(draft)
    if (!result.error) {
        router.push('/admin/partners')
    }
}

const uploadLogo = async (event) => {
    const file = event.target.files?.[0]
    if (!file) return
    const result = await mediaService.upload(file, 'partners')
    if (!result.error) {
        draft.logo_url = result.data.url
        previewUrl.value = result.data.url
    }
}

const removeLogo = async () => {
    if (draft.logo_url?.includes('/storage/')) {
        const path = draft.logo_url.split('/storage/')[1]
        if (path) await mediaService.remove(path)
    }
    draft.logo_url = ''
    previewUrl.value = ''
}

const toggle = async (row) => {
    await togglePartnerStatus(row.id, !row.is_active)
    await fetchPartners({ admin: true, include_inactive: true })
}

const remove = async (id) => {
    if (!window.confirm('Delete this partner?')) return
    await deletePartner(id)
    await fetchPartners({ admin: true, include_inactive: true })
}

onMounted(async () => {
    if (isListMode.value) await fetchPartners({ admin: true, include_inactive: true })
    else await loadCurrent()
})
watch(
    () => route.fullPath,
    async () => {
        if (isListMode.value) {
            await fetchPartners({ admin: true, include_inactive: true })
        } else {
            await loadCurrent()
        }
    },
    { immediate: true }
)
</script>
