<template>
    <AdminCrudShell
        :title="title"
        :create-to="isListMode ? '/admin/volunteers/create' : ''"
    >

        <!-- ================= RO‘YXAT ================= -->
        <template v-if="isListMode">
            <ListState
                :loading="loading"
                :error="error"
                :empty="volunteers.length === 0"
            >

                <AdminTable :columns="columns" :rows="volunteers">

                    <!-- HOLAT -->
                    <template #cell-status="{ row }">
                        <StatusBadge
                            :status="row.status"
                            :map="VOLUNTEER_STATUSES"
                        />
                    </template>

                    <!-- AMALLAR -->
                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-3">

                            <!-- KO‘RISH -->
                            <router-link
                                :to="`/admin/volunteers/${row.id}`"
                                class="p-2 rounded-md hover:bg-blue-50 text-blue-600"
                                title="Ko‘rish"
                            >
                                <Eye class="w-5 h-5" />
                            </router-link>

                            <!-- TAHRIRLASH -->
                            <router-link
                                :to="`/admin/volunteers/${row.id}/edit`"
                                class="p-2 rounded-md hover:bg-amber-50 text-amber-600"
                                title="Tahrirlash"
                            >
                                <Pencil class="w-5 h-5" />
                            </router-link>

                            <!-- O‘CHIRISH -->
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

        <!-- ================= KO‘RISH ================= -->
        <template v-else-if="isViewMode && current">
            <div class="bg-white p-6 rounded-xl shadow space-y-2 text-sm">

                <p><b>Ism:</b> {{ current.full_name }}</p>
                <p><b>Email:</b> {{ current.email }}</p>
                <p><b>Telefon:</b> {{ current.phone }}</p>
                <p><b>Shahar:</b> {{ current.city }}</p>
                <p><b>Qiziqish yo‘nalishi:</b> {{ current.role_interest }}</p>
                <p><b>Tajriba:</b> {{ current.experience }}</p>
                <p><b>Tavsif:</b> {{ current.motivation }}</p>

                <p class="flex items-center gap-2">
                    <b>Holat:</b>
                    <StatusBadge
                        :status="current.status"
                        :map="VOLUNTEER_STATUSES"
                    />
                </p>

            </div>
        </template>

        <!-- ================= FORM ================= -->
        <template v-else>
            <form
                class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-2 gap-4"
                @submit.prevent="save"
            >

                <!-- ISM -->
                <div>
                    <label class="label">To‘liq ism</label>
                    <input v-model="form.full_name" class="input" placeholder="Masalan: Ali Valiyev" />
                </div>

                <!-- EMAIL -->
                <div>
                    <label class="label">Email manzil</label>
                    <input v-model="form.email" class="input" placeholder="example@mail.com" />
                </div>

                <!-- TELEFON -->
                <div>
                    <label class="label">Telefon raqam</label>
                    <input v-model="form.phone" class="input" placeholder="+998 90 123 45 67" />
                </div>

                <!-- SHAHAR -->
                <div>
                    <label class="label">Shahar</label>
                    <input v-model="form.city" class="input" placeholder="Masalan: Toshkent" />
                </div>

                <!-- YO‘NALISH -->
                <div>
                    <label class="label">Qiziqish yo‘nalishi</label>
                    <input v-model="form.role_interest" class="input" placeholder="Masalan: Tibbiyot" />
                </div>

                <!-- TAJRIBA -->
                <div>
                    <label class="label">Tajriba</label>
                    <input v-model="form.experience" class="input" placeholder="Masalan: 2 yil volontyorlik" />
                </div>

                <!-- MOTIVATSIYA -->
                <div class="md:col-span-2">
                    <label class="label">Motivatsiya</label>
                    <textarea
                        v-model="form.motivation"
                        class="input"
                        rows="4"
                        placeholder="Nima sababdan ko‘ngilli bo‘lmoqchisiz?"
                    />
                </div>

                <!-- BUTTON -->
                <div class="md:col-span-2 flex gap-3 mt-2 justify-end">
                    <button class="btn-primary">Saqlash</button>

                    <router-link to="/admin/volunteers" class="btn-secondary">
                        Bekor qilish
                    </router-link>
                </div>

            </form>
        </template>

    </AdminCrudShell>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useVolunteers } from '@/composables/useVolunteers'
import volunteerService from '@/services/volunteerService'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import ListState from '@/components/shared/ListState.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'

import { VOLUNTEER_STATUSES } from '@/constants/statuses'
import { Eye, Pencil, Trash2 } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

const { volunteers, loading, error, fetchVolunteers, updateVolunteer, deleteVolunteer, submitVolunteer } = useVolunteers()

const current = ref(null)

const isListMode = computed(() => route.name === 'admin-volunteers')
const isViewMode = computed(() => route.name === 'admin-volunteers-view')
const isEditMode = computed(() => route.name === 'admin-volunteers-edit')
const isCreateMode = computed(() => route.name === 'admin-volunteers-create')

const title = computed(() => {
    if (isListMode.value) return 'Ko‘ngillilar ro‘yxati'
    if (isCreateMode.value) return 'Yangi ko‘ngilli qo‘shish'
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
    availability: '',
    motivation: '',
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

const loadCurrent = async () => {
    const res = await volunteerService.getById(route.params.id)
    current.value = res.data

    if (isEditMode.value) {
        Object.assign(form, res.data)
    }
}

const save = async () => {
    const res = isEditMode.value
        ? await updateVolunteer(route.params.id, form)
        : await submitVolunteer(form)

    if (!res.error) router.push('/admin/volunteers')
}

const remove = async (id) => {
    if (!confirm('O‘chirishni tasdiqlaysizmi?')) return
    await deleteVolunteer(id)
    await fetchVolunteers()
}

watch(
    () => route.fullPath,
    async () => {
        if (isListMode.value) await fetchVolunteers()
        else await loadCurrent()
    },
    { immediate: true }
)

onMounted(async () => {
    if (isListMode.value) await fetchVolunteers()
})
</script>

<style scoped>
.card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

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

.icon-btn {
    padding: 8px;
    border-radius: 8px;
    transition: 0.2s;
}
</style>
