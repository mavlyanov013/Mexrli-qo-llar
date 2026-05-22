<template>
    <AdminCrudShell :title="title" :create-to="''">

        <!-- ================= LIST ================= -->
        <template v-if="isListMode">
            <ListState :loading="loading" :error="error" :empty="messages.length === 0">

                <AdminTable :columns="columns" :rows="messages">

                    <!-- STATUS -->
                    <template #cell-status="{ row }">
                        <StatusBadge :status="row.status" :map="MESSAGE_STATUSES" />
                    </template>

                    <!-- DATE -->
                    <template #cell-created_at="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>

                    <!-- ACTIONS -->
                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-2">

                            <!-- VIEW (NO ROUTE) -->
                            <button
                                @click="openMessage(row.id)"
                                class="p-2 text-blue-600"
                            >
                                <Eye class="w-5 h-5" />
                            </button>

                            <!-- MARK READ -->
                            <button
                                v-if="row.status !== 'read'"
                                @click="markRead(row.id)"
                                class="p-2 rounded-md hover:bg-green-50 text-green-600"
                            >
                                <Check class="w-5 h-5" />
                            </button>

                            <!-- DELETE -->
                            <button
                                @click="remove(row.id)"
                                class="p-2 rounded-md hover:bg-red-50 text-red-600"
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
                :summary="`${meta.total || 0} ta xabar`"
                @change="fetchPage"
            />
        </template>

        <!-- ================= VIEW ================= -->
        <template v-else-if="isViewMode && current">
            <div class="bg-white p-5 rounded-xl shadow space-y-3">

                <!-- BACK BUTTON -->
                <button
                    @click="closeMessage"
                    class="text-sm text-blue-600 mb-3"
                >
                    ← Ortga
                </button>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <p><b>Ism:</b> {{ current.full_name }}</p>
                    <p><b>Email:</b> {{ current.email }}</p>
                    <p><b>Telefon:</b> {{ current.phone }}</p>
                    <p><b>Mavzu:</b> {{ current.subject }}</p>
                    <p><b>Status:</b>
                        <StatusBadge :status="current.status" :map="MESSAGE_STATUSES" />
                    </p>
                    <p><b>Sana:</b> {{ formatDate(current.created_at) }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg text-sm">
                    {{ current.message }}
                </div>

            </div>
        </template>

        <!-- ================= EMPTY ================= -->
        <template v-else>
            <div class="text-sm text-gray-500 p-5">
                Bu sahifada create yo‘q. Faqat xabarlar boshqaruvi mavjud.
            </div>
        </template>

    </AdminCrudShell>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { MESSAGE_STATUSES } from '@/constants/statuses'
import { useMessages } from '@/composables/useMessages'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'

import { Eye, Trash2, Check } from 'lucide-vue-next'

const { t } = useI18n()

const {
    messages,
    meta,
    loading,
    error,
    fetchMessages,
    updateMessage,
    deleteMessage
} = useMessages()

const currentPage = ref(1)
const fetchPage = (page = 1) => {
    currentPage.value = page
    return fetchMessages({ page, per_page: 15 })
}

/* ================= STATE (ROUTERSIZ) ================= */
const selectedId = ref(null)
const current = ref(null)

/* ================= MODE ================= */
const isListMode = computed(() => selectedId.value === null)
const isViewMode = computed(() => selectedId.value !== null)

const title = computed(() => {
    if (isListMode.value) return t('admin.messages')
    if (isViewMode.value) return 'Xabar tafsiloti'
    return 'Xabarlar'
})

/* ================= TABLE ================= */
const columns = [
    { key: 'full_name', label: 'Ism' },
    { key: 'email', label: 'Email' },
    { key: 'subject', label: 'Mavzu' },
    { key: 'created_at', label: 'Sana' },
    { key: 'status', label: 'Holat' },
    { key: 'actions', label: 'Amallar' },
]

/* ================= ACTIONS ================= */

const formatDate = (date) =>
    date ? new Date(date).toLocaleString() : '-'

/* OPEN MESSAGE */
const openMessage = async (id) => {
    selectedId.value = id

    try {
        const token = localStorage.getItem('token')

        const res = await fetch(`/api/v1/admin/contact-messages/${id}`, {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json'
            }
        })

        if (!res.ok) {
            throw new Error(`API error: ${res.status}`)
        }

        const json = await res.json()
        current.value = json.data

    } catch (err) {
        console.error('Message load error:', err)
    }
}

/* CLOSE MESSAGE */
const closeMessage = () => {
    selectedId.value = null
    current.value = null
}

/* MARK READ */
const markRead = async (id) => {
    const res = await updateMessage(id, { status: 'o\'qing' })
    if (!res.error) await fetchPage(currentPage.value)
}

/* DELETE */
const remove = async (id) => {
    if (!confirm('Xabar o‘chirilsinmi?')) return
    const res = await deleteMessage(id)
    if (!res.error) await fetchPage(currentPage.value)
}

/* INIT */
onMounted(() => {
    fetchPage(1)
})
</script>
