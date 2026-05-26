<template>
    <AdminCrudShell :title="title">
        <p v-if="successMessage" class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ successMessage }}
        </p>

        <!-- LIST -->
        <template v-if="isListMode">
            <AdminTable :columns="columns" :rows="rows">
                <template #cell-category="{ row }">
                    {{ HELP_REQUEST_CATEGORIES[row.category] ?? row.category }}
                </template>

                <template #cell-status="{ row }">
                    <StatusBadge :status="normalizeStatus(row.status)" :map="HELP_REQUEST_STATUS" />
                </template>

                <template #cell-created_at="{ row }">
                    {{ formatDate(row.created_at) }}
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="`/admin/help-requests/${row.id}`"
                            class="p-2 rounded-md hover:bg-blue-50 text-blue-600"
                            title="Ko‘rish"
                        >
                            <Eye class="w-5 h-5" />
                        </router-link>
                    </div>
                </template>
            </AdminTable>

            <AdminPagination
                v-if="meta && meta.last_page > 1"
                :current-page="meta.current_page || 1"
                :last-page="meta.last_page || 1"
                :summary="`${meta.total || 0} ta so‘rov`"
                @change="fetchPage"
            />
        </template>

        <!-- VIEW -->
        <template v-else-if="isViewMode && current">
            <div class="bg-white p-6 rounded-xl shadow space-y-5">
                <router-link
                    to="/admin/help-requests"
                    class="text-sm text-blue-600 hover:underline"
                >
                    ← Ro‘yxatga qaytish
                </router-link>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">F.I.SH</p>
                        <p class="font-semibold text-gray-900">{{ current.full_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Telefon</p>
                        <p class="font-semibold text-gray-900">{{ current.phone }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Shahar</p>
                        <p class="font-semibold text-gray-900">{{ current.city || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Kategoriya</p>
                        <p class="font-semibold text-gray-900">
                            {{ HELP_REQUEST_CATEGORIES[current.category] ?? current.category }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Yuborilgan sana</p>
                        <p class="font-semibold text-gray-900">{{ formatDate(current.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Holat</p>
                        <StatusBadge :status="normalizeStatus(current.status)" :map="HELP_REQUEST_STATUS" />
                    </div>
                </div>

                <div>
                    <p class="text-gray-500 text-sm mb-1">Tavsif</p>
                    <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700 whitespace-pre-wrap">
                        {{ current.description || current.situation_description || '—' }}
                    </div>
                </div>

                <div v-if="imageItems.length">
                    <p class="text-gray-500 text-sm mb-2">Yuborilgan rasmlar / hujjatlar</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <template v-for="(item, index) in imageItems" :key="`${item.url}-${index}`">
                            <a
                                v-if="item.isImage"
                                :href="item.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block rounded-lg border overflow-hidden"
                            >
                                <img :src="item.url" :alt="item.label" class="h-32 w-full object-cover" />
                            </a>
                            <a
                                v-else
                                :href="item.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex h-32 items-center justify-center rounded-lg border bg-gray-50 px-3 text-center text-xs text-blue-600"
                            >
                                {{ item.label }}
                            </a>
                        </template>
                    </div>
                </div>

                <div v-if="current.case_id" class="rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-700">
                    Bog‘langan holat: #{{ current.case_id }}
                    <router-link :to="`/admin/cases/${current.case_id}/edit`" class="ml-2 underline">
                        Holatni ochish
                    </router-link>
                </div>

                <div class="border-t pt-5 space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Holatni o‘zgartirish</label>
                    <select v-model="statusForm.status" class="input max-w-md">
                        <option
                            v-for="option in HELP_REQUEST_STATUS_OPTIONS"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>

                    <textarea
                        v-model="statusForm.admin_notes"
                        rows="3"
                        class="input"
                        :placeholder="t('admin.placeholders.adminNote')"
                    />

                    <p v-if="statusError" class="text-sm text-red-600">{{ statusError }}</p>

                    <button
                        type="button"
                        class="btn-primary"
                        :disabled="savingStatus"
                        @click="saveStatus"
                    >
                        {{ savingStatus ? 'Saqlanmoqda...' : 'Holatni saqlash' }}
                    </button>
                </div>
            </div>
        </template>

        <div v-else-if="loading" class="text-sm text-gray-500">Yuklanmoqda...</div>
    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Eye } from 'lucide-vue-next'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import helpRequestService from '@/services/helpRequestService'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import {
    HELP_REQUEST_CATEGORIES,
    HELP_REQUEST_STATUS,
    HELP_REQUEST_STATUS_OPTIONS,
} from '@/constants/statuses.js'

import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const rows = ref([])
const meta = ref(null)
const currentPage = ref(1)
const current = ref(null)
const loading = ref(false)
const savingStatus = ref(false)
const statusError = ref('')
const successMessage = ref('')

const isListMode = computed(() => route.name === 'admin-help-requests')
const isViewMode = computed(() => route.name === 'admin-help-requests-view')

const title = computed(() =>
    isListMode.value ? 'Yordam so‘rovlari' : 'Yordam so‘rovi'
)

const statusForm = reactive({
    status: 'pending',
    admin_notes: '',
})

const columns = [
    { key: 'full_name', label: 'F.I.SH' },
    { key: 'phone', label: 'Telefon' },
    { key: 'category', label: 'Kategoriya' },
    { key: 'status', label: 'Holat' },
    { key: 'created_at', label: 'Sana' },
    { key: 'actions', label: 'Amallar' },
]

const normalizeStatus = (status) => {
    if (status === 'approved') return 'tasdiqlandi'
    if (status === 'rejected') return 'rad_etildi'
    return status
}

const resolveAssetUrl = (value) => {
    if (!value) return null
    if (typeof value === 'string') {
        if (value.startsWith('http') || value.startsWith('/')) return value
        return `/storage/${value.replace(/^\/+/, '')}`
    }
    if (typeof value === 'object') {
        return value.url || value.path || null
    }
    return null
}

const imageItems = computed(() => {
    if (!current.value) return []

    const sources = [
        ...(current.value.attachments || []),
        ...(current.value.medical_documents || []),
        ...(current.value.photos || []),
    ]

    return sources
        .map((item, index) => {
            const url = resolveAssetUrl(item)
            if (!url) {
                const label = typeof item === 'string' ? item : `Fayl ${index + 1}`
                return { url: '#', label, isImage: false }
            }

            const isImage = /\.(jpg|jpeg|png|gif|webp|bmp)$/i.test(url) || url.includes('/storage/')

            return {
                url,
                label: `Rasm ${index + 1}`,
                isImage,
            }
        })
        .filter((item) => item.url)
})

const formatDate = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleString('uz-UZ')
}

const fetchPage = async (page = 1) => {
    currentPage.value = page
    const result = await helpRequestService.fetchList({ page, per_page: 15 })
    rows.value = result.data || []
    meta.value = result.meta
}

const loadCurrent = async () => {
    loading.value = true
    statusError.value = ''

    try {
        const res = await helpRequestService.getById(route.params.id)
        current.value = res.data ?? null

        if (current.value) {
            statusForm.status = normalizeStatus(current.value.status)
            statusForm.admin_notes = current.value.admin_notes || ''
        }
    } catch (error) {
        current.value = null
        statusError.value = error?.response?.data?.message || 'Ma’lumot yuklanmadi'
    } finally {
        loading.value = false
    }
}

const saveStatus = async () => {
    if (!current.value) return

    savingStatus.value = true
    statusError.value = ''

    try {
        const res = await helpRequestService.updateStatus(current.value.id, {
            status: statusForm.status,
            admin_notes: statusForm.admin_notes,
        })

        if (statusForm.status === 'tasdiqlandi' || res.redirect_to === 'cases') {
            await router.push({
                name: 'admin-cases',
                query: {
                    success: res.message || 'So‘rov tasdiqlandi va holat yaratildi',
                    caseId: res.case_id || '',
                },
            })
            return
        }

        successMessage.value = res.message || 'Holat yangilandi'
        await loadCurrent()
    } catch (error) {
        statusError.value = error?.response?.data?.message || 'Holatni saqlab bo‘lmadi'
    } finally {
        savingStatus.value = false
    }
}

const hydrate = async () => {
    successMessage.value = route.query.success || ''

    if (isListMode.value) {
        await fetchPage(currentPage.value)
        return
    }

    if (isViewMode.value) {
        await loadCurrent()
    }
}

onMounted(hydrate)
watch(() => route.fullPath, hydrate)
</script>

<style scoped>
.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
}
.btn-primary {
    background: #2A7DE1;
    color: #fff;
    border-radius: 12px;
    padding: 10px 18px;
    font-size: 14px;
}
</style>
