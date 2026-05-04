<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import caseService from '@/services/caseService'
import donationService from '@/services/donationService'
import helpRequestService from '@/services/helpRequestService'
import volunteerService from '@/services/volunteerService'
import contactService from '@/services/contactService'
import blogService from '@/services/blogService'
import paymentService from '@/services/paymentService'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import AdminEmptyState from '@/admin/components/common/AdminEmptyState.vue'
import AdminPagination from '@/admin/components/common/AdminPagination.vue'
import AdminToast from '@/admin/components/common/AdminToast.vue'

const { t } = useI18n()

const props = defineProps({
    activeTab: {
        type: String,
        default: 'overview'
    }
})

const cases = ref([])
const donations = ref([])
const helpRequests = ref([])
const volunteers = ref([])
const messages = ref([])
const posts = ref([])
const payments = ref([])
const loading = ref(false)
const loadError = ref(false)
const search = ref('')
const paymentsMeta = ref(null)
const paymentsPage = ref(1)

const loadPayments = async (page = 1) => {
    const paymentsRes = await paymentService.getAll({ per_page: 20, page })
    payments.value = Array.isArray(paymentsRes?.data) ? paymentsRes.data : []
    paymentsMeta.value = paymentsRes?.meta ?? null
    paymentsPage.value = page
}

const loadData = async () => {
    loading.value = true
    loadError.value = false

    try {
        const [
            casesRes,
            donationsRes,
            helpRequestsRes,
            volunteersRes,
            messagesRes,
            postsRes,
        ] = await Promise.all([
            caseService.getAllCases(),
            donationService.getAllDonations(),
            helpRequestService.getAll(),
            volunteerService.getAll(),
            contactService.getAll(),
            blogService.getAll(),
            loadPayments(paymentsPage.value),
        ])

        cases.value = Array.isArray(casesRes?.data) ? casesRes.data : Array.isArray(casesRes) ? casesRes : []
        donations.value = Array.isArray(donationsRes?.data) ? donationsRes.data : Array.isArray(donationsRes) ? donationsRes : []
        helpRequests.value = Array.isArray(helpRequestsRes?.data) ? helpRequestsRes.data : Array.isArray(helpRequestsRes) ? helpRequestsRes : []
        volunteers.value = Array.isArray(volunteersRes?.data) ? volunteersRes.data : Array.isArray(volunteersRes) ? volunteersRes : []
        messages.value = Array.isArray(messagesRes?.data) ? messagesRes.data : Array.isArray(messagesRes) ? messagesRes : []
        posts.value = Array.isArray(postsRes?.data) ? postsRes.data : Array.isArray(postsRes) ? postsRes : []
    } catch (error) {
        console.error('Admin data load error:', error)
        loadError.value = true
        cases.value = []
        donations.value = []
        helpRequests.value = []
        volunteers.value = []
        messages.value = []
        posts.value = []
        payments.value = []
        paymentsMeta.value = null
    } finally {
        loading.value = false
    }
}

onMounted(loadData)

const formatMoney = (value) => `${Number(value || 0).toLocaleString()} UZS`

const badgeClass = (value) => {
    const status = String(value || '').toLowerCase()

    if (['success', 'completed', 'approved', 'active', 'open', 'new'].includes(status)) {
        return 'bg-green-50 text-green-700'
    }

    if (['pending', 'interviewing', 'in_progress'].includes(status)) {
        return 'bg-yellow-50 text-yellow-700'
    }

    if (['cancelled', 'canceled', 'failed', 'rejected', 'closed'].includes(status)) {
        return 'bg-red-50 text-red-700'
    }

    return 'bg-gray-100 text-gray-700'
}

const totalDonated = computed(() =>
    donations.value.reduce((sum, d) => sum + Number(d.amount || 0), 0)
)

const activeCasesCount = computed(() =>
    cases.value.filter(c => ['active', 'open', 'in_progress'].includes(String(c.status || '').toLowerCase())).length
)

const pendingHelpRequests = computed(() =>
    helpRequests.value.filter(h => h.status === 'pending').length
)

const newMessages = computed(() =>
    messages.value.filter(m => m.status === 'new').length
)

const paymentCount = computed(() => payments.value.length)

const filteredPayments = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return payments.value

    return payments.value.filter((payment) =>
        String(payment.provider || '').toLowerCase().includes(q) ||
        String(payment.transaction_id || '').toLowerCase().includes(q) ||
        String(payment.payer_reference || '').toLowerCase().includes(q) ||
        String(payment.status || '').toLowerCase().includes(q)
    )
})

const paymentColumns = computed(() => ([
    { key: 'provider', label: t('admin.provider') },
    { key: 'transaction_id', label: t('admin.transactionId') },
    { key: 'payer_reference', label: t('admin.payer') },
    { key: 'amount', label: t('admin.amount') },
    { key: 'status', label: t('admin.status') },
]))

const paymentsSummary = computed(() => {
    const meta = paymentsMeta.value || {}
    if (!meta.total) return ''
    return `${meta.current_page}/${meta.last_page} • ${meta.total}`
})

const changePaymentsPage = async (page) => {
    try {
        await loadPayments(page)
        loadError.value = false
    } catch (error) {
        console.error('Payments page load error:', error)
        loadError.value = true
    }
}

const filteredDonations = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return donations.value

    return donations.value.filter((donation) =>
        String(donation.donor_name || '').toLowerCase().includes(q) ||
        String(donation.amount || '').toLowerCase().includes(q) ||
        String(donation.type || '').toLowerCase().includes(q)
    )
})

const filteredCases = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return cases.value

    return cases.value.filter((c) =>
        String(c.name || '').toLowerCase().includes(q) ||
        String(c.status || '').toLowerCase().includes(q)
    )
})

const filteredVolunteers = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return volunteers.value

    return volunteers.value.filter((v) =>
        String(v.full_name || '').toLowerCase().includes(q) ||
        String(v.email || '').toLowerCase().includes(q) ||
        String(v.role_interest || '').toLowerCase().includes(q) ||
        String(v.status || '').toLowerCase().includes(q)
    )
})
</script>

<template>
    <div class="space-y-6">
        <AdminToast
            :show="loadError"
            :message="t('admin.loading')"
            tone="error"
        />
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ t('admin.dashboard') }}</h1>

            <div
                v-if="props.activeTab !== 'overview'"
                class="w-full lg:w-80"
            >
                <input
                    v-model="search"
                    type="text"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none"
                    :placeholder="t('admin.search') || t('admin.searchPlaceholder')"
                />
            </div>
        </div>

        <div v-if="loading" class="text-gray-500">
            {{ t('admin.loading') }}
        </div>

        <template v-else>
            <div v-if="props.activeTab === 'overview'" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                    <div class="bg-white rounded-2xl p-5 border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ formatMoney(totalDonated) }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.totalDonations') }}</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ activeCasesCount }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.activeCases') }}</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ pendingHelpRequests }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.pendingHelpRequests') }}</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ newMessages }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.newMessages') }}</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ paymentCount }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.payments') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-bold text-gray-900">{{ t('admin.payments') }}</h2>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div
                                v-for="payment in payments.slice(0, 5)"
                                :key="payment.id"
                                class="p-4 flex items-center justify-between gap-3"
                            >
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 uppercase">{{ payment.provider }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ payment.transaction_id }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-semibold text-gray-900">{{ formatMoney(payment.amount) }}</p>
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(payment.status)">
                                        {{ payment.status }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="payments.length === 0" class="p-4 text-sm text-gray-500">
                                {{ t('admin.noPayments') }}
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-bold text-gray-900">{{ t('admin.volunteers') }}</h2>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div
                                v-for="v in volunteers.slice(0, 5)"
                                :key="v.id"
                                class="p-4 flex items-center justify-between gap-3"
                            >
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900">{{ v.full_name }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ v.email }}</p>
                                </div>
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium shrink-0" :class="badgeClass(v.status)">
                                    {{ t(`adminVolunteer.statuses.${v.status || 'pending'}`) }}
                                </span>
                            </div>

                            <div v-if="volunteers.length === 0" class="p-4 text-sm text-gray-500">
                                {{ t('admin.noVolunteers') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="props.activeTab === 'payments'" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-bold">{{ t('admin.payments') }} ({{ filteredPayments.length }})</h2>
                </div>

                <AdminTable
                    v-if="filteredPayments.length > 0"
                    :columns="paymentColumns"
                    :rows="filteredPayments"
                >
                    <template #cell-provider="{ value }">
                        <span class="uppercase font-medium">{{ value }}</span>
                    </template>
                    <template #cell-payer_reference="{ value }">
                        {{ value || t('common.unknown') }}
                    </template>
                    <template #cell-amount="{ value }">
                        {{ formatMoney(value) }}
                    </template>
                    <template #cell-status="{ value }">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(value)">
                            {{ value }}
                        </span>
                    </template>
                </AdminTable>

                <AdminEmptyState
                    v-else
                    :message="t('admin.noPayments')"
                />

                <AdminPagination
                    v-if="paymentsMeta && !search.trim()"
                    :current-page="paymentsMeta.current_page || 1"
                    :last-page="paymentsMeta.last_page || 1"
                    :summary="paymentsSummary"
                    @change="changePaymentsPage"
                />
            </div>

            <div v-if="props.activeTab === 'cases'" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-bold">{{ t('admin.cases') }} ({{ filteredCases.length }})</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4">{{ t('admin.name') }}</th>
                            <th class="text-left p-4">{{ t('admin.status') }}</th>
                            <th class="text-left p-4">{{ t('admin.goal') }}</th>
                            <th class="text-left p-4">{{ t('admin.raised') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="c in filteredCases" :key="c.id" class="border-t">
                            <td class="p-4">{{ c.name }}</td>
                            <td class="p-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(c.status)">
                                    {{ c.status }}
                                </span>
                            </td>
                            <td class="p-4">{{ formatMoney(c.goal_amount) }}</td>
                            <td class="p-4">{{ formatMoney(c.raised_amount) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="props.activeTab === 'donations'" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-bold">{{ t('admin.donations') }} ({{ filteredDonations.length }})</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4">{{ t('admin.donor') }}</th>
                            <th class="text-left p-4">{{ t('admin.amount') }}</th>
                            <th class="text-left p-4">{{ t('admin.type') }}</th>
                            <th class="text-left p-4">{{ t('admin.date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="d in filteredDonations" :key="d.id" class="border-t">
                            <td class="p-4">{{ d.is_anonymous ? t('admin.anonymous') : d.donor_name }}</td>
                            <td class="p-4">{{ formatMoney(d.amount) }}</td>
                            <td class="p-4">{{ d.type || t('common.unknown') }}</td>
                            <td class="p-4">{{ d.created_at || d.created_date || t('common.unknown') }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredDonations.length === 0" class="p-4 text-sm text-gray-500">
                    {{ t('admin.noDonations') }}
                </div>
            </div>

            <div v-if="props.activeTab === 'help-requests'" class="space-y-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ t('admin.helpRequests') }} ({{ helpRequests.length }})</h2>

                <div
                    v-for="h in helpRequests"
                    :key="h.id"
                    class="bg-white rounded-2xl border border-gray-100 p-5"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-lg">{{ h.full_name }}</p>
                            <p class="text-sm text-gray-500">{{ t('admin.phone') }}: {{ h.phone }} • {{ t('admin.city') }}: {{ h.city }}</p>
                        </div>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium shrink-0" :class="badgeClass(h.status)">
                            {{ h.status }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-gray-600">{{ h.situation_description }}</p>
                </div>

                <div v-if="helpRequests.length === 0" class="text-sm text-gray-500">
                    {{ t('admin.noHelpRequests') }}
                </div>
            </div>

            <div v-if="props.activeTab === 'volunteers'" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-bold">{{ t('admin.volunteers') }} ({{ filteredVolunteers.length }})</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.fullName') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.email') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.role') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.availability') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.status') }}</th>
                            <th class="text-left p-4">{{ t('adminVolunteer.fields.city') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="v in filteredVolunteers" :key="v.id" class="border-t">
                            <td class="p-4">{{ v.full_name }}</td>
                            <td class="p-4">{{ v.email }}</td>
                            <td class="p-4">
                                {{ t(`adminVolunteer.roles.${v.role_interest || 'other'}`) }}
                            </td>
                            <td class="p-4">
                                {{ t(`adminVolunteer.availability.${v.availability || 'flexible'}`) }}
                            </td>
                            <td class="p-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(v.status)">
                                    {{ t(`adminVolunteer.statuses.${v.status || 'pending'}`) }}
                                </span>
                            </td>
                            <td class="p-4">{{ v.city || t('common.unknown') }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredVolunteers.length === 0" class="p-4 text-sm text-gray-500">
                    {{ t('admin.noVolunteers') }}
                </div>
            </div>

            <div v-if="props.activeTab === 'messages'" class="space-y-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ t('admin.messages') }} ({{ messages.length }})</h2>

                <div
                    v-for="m in messages"
                    :key="m.id"
                    class="bg-white rounded-2xl border border-gray-100 p-5"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ m.name }}</p>
                            <p class="text-sm text-gray-500">{{ m.email }}</p>
                        </div>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badgeClass(m.status)">
                            {{ m.status || t('common.unknown') }}
                        </span>
                    </div>
                    <p class="text-sm font-medium mt-3">{{ t('admin.subject') }}: {{ m.subject }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ m.message }}</p>
                </div>

                <div v-if="messages.length === 0" class="text-sm text-gray-500">
                    {{ t('admin.noMessages') }}
                </div>
            </div>

            <div v-if="props.activeTab === 'blog'" class="space-y-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ t('admin.blog') }} ({{ posts.length }})</h2>

                <div
                    v-for="p in posts"
                    :key="p.id"
                    class="bg-white rounded-2xl border border-gray-100 p-5"
                >
                    <p class="font-semibold">{{ p.title }}</p>
                    <p class="text-sm text-gray-500">{{ t('admin.category') }}: {{ p.category }}</p>
                </div>

                <div v-if="posts.length === 0" class="text-sm text-gray-500">
                    {{ t('admin.noBlogPosts') }}
                </div>
            </div>
        </template>
    </div>
</template>
