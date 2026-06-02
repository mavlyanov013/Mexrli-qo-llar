<template>
    <AdminCrudShell :title="title">
        <template v-if="isListMode">
            <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                <AdminSearchInput
                    v-model="filters.q"
                    placeholder="Ism, email yoki telefon bo‘yicha qidirish"
                />

                <select
                    v-model="filters.status"
                    class="h-10 rounded-lg border border-gray-300 px-3 text-sm"
                >
                    <option value="">Barcha holatlar</option>
                    <option
                        v-for="option in VOLUNTEER_STATUS_OPTIONS"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>

                <button
                    type="button"
                    class="h-10 rounded-lg bg-[#2A7DE1] px-4 text-sm font-medium text-white"
                    @click="applyFilters"
                >
                    Filtrlash
                </button>

                <button
                    type="button"
                    class="h-10 rounded-lg border border-gray-300 px-4 text-sm text-gray-700"
                    @click="clearFilters"
                >
                    Tozalash
                </button>
            </div>

            <ListState
                :loading="loading"
                :error="error"
                :empty="volunteers.length === 0"
            >
                <AdminTable :columns="columns" :rows="volunteers">
                    <template #cell-status="{ row }">
                        <StatusBadge
                            :status="row.status"
                            :map="VOLUNTEER_STATUSES"
                        />
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-3">
                            <router-link
                                :to="`/admin/volunteers/${row.id}`"
                                class="p-2 rounded-md hover:bg-blue-50 text-blue-600"
                                title="Ko‘rish"
                            >
                                <Eye class="w-5 h-5" />
                            </router-link>

                            <router-link
                                :to="`/admin/volunteers/${row.id}/edit`"
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
                v-if="meta && meta.last_page > 1"
                :current-page="meta.current_page || 1"
                :last-page="meta.last_page || 1"
                :summary="`${meta.total || 0} ta ariza`"
                @change="fetchPage"
            />
        </template>

        <template v-else-if="isViewMode && current">
            <div class="bg-white p-6 rounded-xl shadow space-y-4 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-900">Ko‘ngilli ma’lumotlari</h3>
                    <router-link
                        :to="`/admin/volunteers/${current.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-100"
                    >
                        <Pencil class="w-4 h-4" />
                        Tahrirlash
                    </router-link>
                </div>

                <p><b>Ism:</b> {{ current.full_name }}</p>
                <p><b>Email:</b> {{ current.email }}</p>
                <p><b>Telefon:</b> {{ current.phone || '—' }}</p>
                <p><b>Shahar:</b> {{ current.city || '—' }}</p>
                <p><b>Qiziqish yo‘nalishi:</b> {{ current.role_interest || '—' }}</p>
                <p><b>Tajriba:</b> {{ current.experience || '—' }}</p>
                <p><b>Motivatsiya:</b> {{ current.motivation || '—' }}</p>

                <div class="max-w-xs">
                    <label class="label">Holat</label>
                    <select
                        v-model="viewStatus"
                        class="input"
                        :disabled="statusSaving"
                        @change="saveStatus"
                    >
                        <option
                            v-for="option in VOLUNTEER_STATUS_OPTIONS"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </template>

        <template v-else-if="isEditMode">
            <form
                class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-2 gap-4"
                @submit.prevent="save"
            >
                <div>
                    <label class="label">To‘liq ism</label>
                    <input v-model="form.full_name" class="input" placeholder="Masalan: Ali Valiyev" required />
                </div>

                <div>
                    <label class="label">Email manzil</label>
                    <input v-model="form.email" type="email" class="input" placeholder="example@mail.com" required />
                </div>

                <div>
                    <label class="label">Telefon raqam</label>
                    <PhoneInput ref="phoneInputRef" v-model="form.phone" input-class="input" />
                </div>

                <div>
                    <label class="label">Shahar</label>
                    <input v-model="form.city" class="input" placeholder="Masalan: Toshkent" />
                </div>

                <div>
                    <label class="label">Qiziqish yo‘nalishi</label>
                    <input v-model="form.role_interest" class="input" placeholder="Masalan: Tibbiyot" />
                </div>

                <div>
                    <label class="label">Tajriba</label>
                    <input v-model="form.experience" class="input" placeholder="Masalan: 2 yil volontyorlik" />
                </div>

                <div>
                    <label class="label">Holat</label>
                    <select v-model="form.status" class="input">
                        <option
                            v-for="option in VOLUNTEER_STATUS_OPTIONS"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="label">Motivatsiya</label>
                    <textarea
                        v-model="form.motivation"
                        class="input"
                        rows="4"
                        placeholder="Nima sababdan ko‘ngilli bo‘lmoqchisiz?"
                    />
                </div>

                <div class="md:col-span-2 flex gap-3 mt-2 justify-end">
                    <button type="submit" class="btn-primary" :disabled="saving">
                        {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>

                    <router-link :to="`/admin/volunteers/${route.params.id}`" class="btn-secondary">
                        Bekor qilish
                    </router-link>
                </div>
            </form>
        </template>
    </AdminCrudShell>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useVolunteers } from '@/composables/useVolunteers'
import volunteerService from '@/services/volunteerService'
import {
    VOLUNTEER_STATUSES,
    VOLUNTEER_STATUS_OPTIONS,
    normalizeVolunteerStatus,
} from '@/admin/constants/volunteerStatuses'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import AdminSearchInput from '@/admin/components/common/AdminSearchInput.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { Eye, Pencil, Trash2 } from 'lucide-vue-next'
import PhoneInput from '@/components/shared/PhoneInput.vue'

const route = useRoute()
const router = useRouter()

const { volunteers, meta, loading, error, fetchVolunteers, updateVolunteer, deleteVolunteer } = useVolunteers()

const filters = reactive({
    q: '',
    status: '',
})

const appliedFilters = reactive({
    q: '',
    status: '',
})

const buildParams = (page = 1) => {
    const params = { page, per_page: 15 }

    if (appliedFilters.q) params.q = appliedFilters.q
    if (appliedFilters.status) params.status = appliedFilters.status

    return params
}

const fetchPage = (page = 1) => fetchVolunteers(buildParams(page))

const applyFilters = () => {
    appliedFilters.q = filters.q.trim()
    appliedFilters.status = filters.status
    fetchPage(1)
}

const clearFilters = () => {
    filters.q = ''
    filters.status = ''
    appliedFilters.q = ''
    appliedFilters.status = ''
    fetchPage(1)
}

const current = ref(null)
const phoneInputRef = ref(null)
const saving = ref(false)
const statusSaving = ref(false)
const viewStatus = ref('rezerv')

const isListMode = computed(() => route.name === 'admin-volunteers')
const isViewMode = computed(() => route.name === 'admin-volunteers-view')
const isEditMode = computed(() => route.name === 'admin-volunteers-edit')

const title = computed(() => {
    if (isListMode.value) return 'Ko‘ngillilar ro‘yxati'
    if (isEditMode.value) return 'Ko‘ngillini tahrirlash'
    return 'Ko‘ngilli ma’lumotlari'
})

const form = reactive({
    full_name: '',
    email: '',
    phone: '',
    city: '',
    role_interest: '',
    experience: '',
    motivation: '',
    status: 'rezerv',
})

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'full_name', label: 'Ism' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Telefon' },
    { key: 'city', label: 'Shahar' },
    { key: 'role_interest', label: 'Yo‘nalish' },
    { key: 'status', label: 'Holat' },
    { key: 'actions', label: 'Amallar' },
]

const assignForm = (data) => {
    Object.assign(form, {
        full_name: data.full_name || '',
        email: data.email || '',
        phone: data.phone || '',
        city: data.city || '',
        role_interest: data.role_interest || '',
        experience: data.experience || '',
        motivation: data.motivation || '',
        status: normalizeVolunteerStatus(data.status),
    })
}

const loadCurrent = async () => {
    const res = await volunteerService.getById(route.params.id)
    current.value = res.data

    if (current.value) {
        viewStatus.value = normalizeVolunteerStatus(current.value.status)
    }

    if (isEditMode.value && current.value) {
        assignForm(current.value)
    }
}

const saveStatus = async () => {
    if (!current.value) return

    statusSaving.value = true
    const res = await updateVolunteer(route.params.id, { status: viewStatus.value })

    if (!res.error) {
        current.value = { ...current.value, status: viewStatus.value }
    }

    statusSaving.value = false
}

const save = async () => {
    if (form.phone && !phoneInputRef.value?.validate()) {
        return
    }

    saving.value = true
    const res = await updateVolunteer(route.params.id, { ...form })
    saving.value = false

    if (!res.error) {
        router.push(`/admin/volunteers/${route.params.id}`)
    }
}

const remove = async (id) => {
    if (!confirm('O‘chirishni tasdiqlaysizmi?')) return
    await deleteVolunteer(id)
    await fetchPage(meta.value?.current_page || 1)
}

watch(
    () => route.fullPath,
    async () => {
        if (isListMode.value) {
            await fetchPage()
            return
        }

        await loadCurrent()
    },
    { immediate: true }
)
</script>

<style scoped>
.label {
    display: block;
    font-size: 13px;
    margin-bottom: 6px;
    color: #555;
}

.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 14px;
}

.input:focus {
    outline: none;
    border-color: #2A7DE1;
    box-shadow: 0 0 0 2px rgba(42,125,225,0.2);
}

.btn-primary {
    background: linear-gradient(135deg, #2A7DE1, #1d5fbf);
    color: white;
    padding: 10px 16px;
    border-radius: 12px;
}

.btn-secondary {
    border: 1px solid #ddd;
    padding: 10px 16px;
    border-radius: 12px;
}
</style>
