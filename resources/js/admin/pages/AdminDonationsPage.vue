<template>
    <AdminCrudShell :title="title" :create-to="isListMode ? '/admin/donations/create' : ''">

        <!-- LIST -->
        <template v-if="isListMode">
            <ListState :loading="loading" :error="error" :empty="donations.length === 0">

                <!-- FILTER -->
                <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-2">

                    <input
                        v-model="filters.search"
                        placeholder="Qidirish donor..."
                        class="h-10 border rounded-lg px-3 text-sm"
                    />

                    <select v-model="filters.status" class="h-10 border rounded-lg px-3 text-sm">
                        <option value="">Barcha statuslar</option>
                        <option value="pending">kutilmoqda</option>
                        <option value="completed">yakunlandi</option>
                        <option value="failed">muvaffaqiyatsiz</option>
                    </select>

                    <button
                        type="button"
                        class="h-10 bg-[#2A7DE1] text-white rounded-lg text-sm px-4"
                        @click="applyFilters"
                    >
                        Filtrlash
                    </button>

                    <button
                        type="button"
                        class="h-10 border rounded-lg text-sm px-4"
                        @click="resetFilters"
                    >
                        Tozalash
                    </button>

                </div>

                <!-- TABLE -->
                <AdminTable :columns="columns" :rows="donations">

                    <template #cell-status="{ row }">
                        <StatusBadge :status="row.status" :map="DONATION_STATUSES" />
                    </template>


                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-3">

                            <router-link
                                :to="`/admin/donations/${row.id}`"
                                class="text-blue-600 hover:scale-110 transition"
                            >
                                <Eye class="w-4 h-4" />
                            </router-link>

                            <router-link
                                :to="`/admin/donations/${row.id}/edit`"
                                class="text-amber-600 hover:scale-110 transition"
                            >
                                <Pencil class="w-4 h-4" />
                            </router-link>

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
                :summary="`${meta.total || 0} ta xayriya`"
                @change="fetchPage"
            />
        </template>

        <!-- VIEW -->
        <template v-else-if="isViewMode && current">
            <div class="space-y-2 text-sm">
                <p><strong>ID:</strong> {{ current.id }}</p>
                <p><strong>Xayriya qiluvchi:</strong> {{ current.donor_name }}</p>
                <p><strong>Telefon:</strong> {{ current.donor_phone }}</p>
                <p><strong>Miqdori:</strong> {{ current.amount }} {{ current.currency }}</p>
                <p>
                    <strong>Holati:</strong>
                    <StatusBadge :status="current.status" :map="DONATION_STATUSES" />
                </p>
            </div>
        </template>

        <!-- CREATE / EDIT -->
        <template v-else>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-3" @submit.prevent="save">

                <div
                    v-if="isCreateMode"
                    class="md:col-span-2 flex h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-700"
                >
                    Naqt pul
                </div>

                <select
                    v-else
                    v-model="form.type"
                    class="h-10 border rounded-lg px-3 text-sm"
                >
                    <option value="manual">naqd pul (qo'lda)</option>
                    <option value="online">onlayn</option>
                </select>

                <input
                    v-model.number="form.amount"
                    type="number"
                    min="0"
                    class="h-10 border rounded-lg px-3 text-sm"
                    placeholder="Miqdori"
                    required
                />

                <input
                    v-model="form.donor_name"
                    class="h-10 border rounded-lg px-3 text-sm"
                    placeholder="Xayriya qiluvchi ismi"
                    required
                />

                <PhoneInput
                    v-if="!isCreateMode"
                    ref="phoneInputRef"
                    v-model="form.donor_phone"
                    input-class="h-10 border rounded-lg px-3 text-sm w-full"
                />

                <input
                    v-model="form.currency"
                    class="h-10 border rounded-lg px-3 text-sm"
                    placeholder="UZS"
                />

                <select
                    v-if="!isCreateMode"
                    v-model="form.status"
                    class="h-10 border rounded-lg px-3 text-sm"
                >
                    <option value="completed">yakunlandi</option>
                    <option value="pending">kutilmoqda</option>
                    <option value="failed">muvaffaqiyatsiz</option>
                </select>

                <div class="md:col-span-2 flex gap-2">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Saqlash
                    </button>

                    <router-link
                        to="/admin/donations"
                        class="border px-4 py-2 rounded-lg"
                    >
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
import { useI18n } from 'vue-i18n'

import { useDonations } from '@/composables/useDonations'
import donationService from '@/services/donationService'
import PhoneInput from '@/components/shared/PhoneInput.vue'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'

import { Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { DONATION_STATUSES } from '@/constants/statuses'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const {
    donations,
    meta,
    loading,
    error,
    fetchDonations,
    deleteDonation,
} = useDonations()

const currentPage = ref(1)

const current = ref(null)
const phoneInputRef = ref(null)

const isListMode = computed(() => route.name === 'admin-donations')
const isCreateMode = computed(() => route.name === 'admin-donations-create')
const isEditMode = computed(() => route.name === 'admin-donations-edit')
const isViewMode = computed(() => route.name === 'admin-donations-view')

const title = computed(() =>
    isListMode.value
        ? t('admin.donations')
        : isEditMode.value
            ? 'Edit donation'
            : isViewMode.value
                ? 'Donation details'
                : 'Create donation'
)

const filters = reactive({
    search: '',
    status: '',
})

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'donor_name', label: 'Ism' },
    { key: 'donor_phone', label: 'Telefon raqam' },
    { key: 'amount', label: 'Jami' },
    { key: 'status', label: 'Holati' },
    { key: 'provider', label: 'Provider' },
    { key: 'actions', label: 'Harakatlar' },
]

const form = reactive({
    type: 'naxt',
    donor_name: '',
    donor_phone: '',
    amount: 0,
    status: 'completed',
    currency: 'UZS',
})

const buildParams = (page = 1) => {
    const params = { admin: true, page, per_page: 15 }
    if (filters.search.trim()) params.search = filters.search.trim()
    if (filters.status) params.status = filters.status
    return params
}

const fetchPage = async (page = 1) => {
    currentPage.value = page
    await fetchDonations(buildParams(page))
}

const applyFilters = () => fetchPage(1)

const resetFilters = () => {
    filters.search = ''
    filters.status = ''
    fetchPage(1)
}

const save = async () => {
    if (!isCreateMode.value && form.donor_phone && !phoneInputRef.value?.validate()) {
        return
    }

    const payload = isCreateMode.value
        ? {
            type: 'naxt',
            amount: form.amount,
            donor_name: form.donor_name,
            currency: form.currency,
            is_manual_cash: true,
        }
        : {
            type: form.type,
            amount: form.amount,
            donor_name: form.donor_name,
            donor_phone: form.donor_phone,
            currency: form.currency,
            status: form.status,
        }

    const res = isCreateMode.value
        ? await donationService.create(payload)
        : await donationService.update(route.params.id, payload)

    if (!res.error) {
        router.push('/admin/donations')
    }
}

const loadCurrent = async () => {
    if (!route.params.id) return

    const res = await donationService.getById(route.params.id)
    current.value = res.data

    if (isEditMode.value && res.data) {
        Object.assign(form, {
            type: res.data.type || 'manual',
            donor_name: res.data.donor_name,
            donor_phone: res.data.donor_phone,
            amount: Number(res.data.amount),
            status: res.data.status,
            currency: res.data.currency || 'UZS',
        })
    }
}

const remove = async (id) => {
    if (!confirm('Delete donation?')) return
    await deleteDonation(id)
    await fetchPage(currentPage.value)
}

watch(() => route.fullPath, async () => {
    if (isListMode.value) await fetchPage(currentPage.value)
    else await loadCurrent()
}, { immediate: true })

onMounted(async () => {
    if (isListMode.value) await fetchPage(1)
    else await loadCurrent()
})
</script>
