<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import {
    HeartPulse,
    HandCoins,
    ClipboardList,
    Mail,
    CreditCard,
    Users,
    ArrowRight,
    TrendingUp,
    AlertCircle,
} from 'lucide-vue-next'
import caseService from '@/services/caseService'
import helpRequestService from '@/services/helpRequestService'
import volunteerService from '@/services/volunteerService'
import contactService from '@/services/contactService'
import donationService from '@/services/donationService'
import paymentService from '@/services/paymentService'
import blogService from '@/services/blogService'
import { usePermissions } from '@/composables/usePermissions'
import { providerLabel } from '@/constants/payments'
import { PAYMENT_STATUSES } from '@/constants/statuses'
import { HELP_REQUEST_STATUS } from '@/constants/statuses'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { formatMoneyAmount, formatAmount } from '@/utils/formatAmount'

const props = defineProps({
    activeTab: {
        type: String,
        default: 'overview',
    },
})

const { t } = useI18n()
const { isSuperAdmin } = usePermissions()

const loading = ref(true)
const loadError = ref(false)

const stats = ref({
    totalCases: 0,
    activeCases: 0,
    pendingHelp: 0,
    newMessages: 0,
    totalDonations: 0,
    onlinePayments: 0,
    volunteers: 0,
})

const recentHelp = ref([])
const recentCases = ref([])
const recentPayments = ref([])
const posts = ref([])

const formatMoney = (value) => formatMoneyAmount(value, "so'm")

const formatDate = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleString('uz-UZ', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const progressPercent = (raised, goal) => {
    const g = Number(goal || 0)
    const r = Number(raised || 0)
    if (g <= 0) return 0
    return Math.min(100, Math.round((r / g) * 100))
}

const statCards = computed(() => {
    const cards = [
        {
            key: 'cases',
            label: 'Faol holatlar',
            value: stats.value.activeCases,
            hint: `${stats.value.totalCases} ta jami`,
            icon: HeartPulse,
            tone: 'blue',
            to: '/admin/cases',
        },
        {
            key: 'help',
            label: 'Kutilayotgan so‘rovlar',
            value: stats.value.pendingHelp,
            hint: 'Yordam so‘rovlari',
            icon: ClipboardList,
            tone: 'amber',
            to: '/admin/help-requests',
        },
        {
            key: 'messages',
            label: 'Yangi xabarlar',
            value: stats.value.newMessages,
            hint: 'Aloqa xabarlari',
            icon: Mail,
            tone: 'violet',
            to: '/admin/messages',
        },
        {
            key: 'volunteers',
            label: 'Ko‘ngillilar',
            value: stats.value.volunteers,
            hint: 'Arizalar ro‘yxati',
            icon: Users,
            tone: 'emerald',
            to: '/admin/volunteers',
        },
    ]

    if (isSuperAdmin.value) {
        cards.unshift({
            key: 'donations',
            label: 'Jami xayriyalar',
            value: formatMoney(stats.value.totalDonations),
            hint: 'Barcha xayriyalar',
            icon: HandCoins,
            tone: 'orange',
            to: '/admin/donations',
            isMoney: true,
        })
        cards.push({
            key: 'payments',
            label: 'Onlayn to‘lovlar',
            value: stats.value.onlinePayments,
            hint: 'To‘lovlar bo‘limi',
            icon: CreditCard,
            tone: 'sky',
            to: '/admin/payments',
        })
    }

    return cards
})

const quickLinks = [
    { to: '/admin/cases/create', label: 'Yangi holat', desc: 'Bemor kampaniyasi' },
    { to: '/admin/help-requests', label: 'Yordam so‘rovlari', desc: 'Tekshirish kerak' },
    { to: '/admin/news', label: 'Yangiliklar', desc: 'Kontent boshqaruvi' },
    { to: '/admin/contact-info', label: 'Aloqa', desc: 'Sayt ma’lumotlari' },
]

const toneClasses = {
    blue: 'from-[#2A7DE1]/10 to-[#2A7DE1]/5 text-[#2A7DE1] border-[#2A7DE1]/20',
    amber: 'from-amber-50 to-orange-50 text-amber-700 border-amber-200',
    violet: 'from-violet-50 to-purple-50 text-violet-700 border-violet-200',
    emerald: 'from-emerald-50 to-green-50 text-emerald-700 border-emerald-200',
    orange: 'from-orange-50 to-amber-50 text-orange-700 border-orange-200',
    sky: 'from-sky-50 to-blue-50 text-sky-700 border-sky-200',
}

const loadOverview = async () => {
    loading.value = true
    loadError.value = false

    try {
        const requests = [
            caseService.fetchList({ admin: true, page: 1, per_page: 6 }),
            caseService.fetchList({ admin: true, status: 'active', page: 1, per_page: 1 }),
            helpRequestService.fetchList({ page: 1, per_page: 5 }),
            helpRequestService.fetchList({ page: 1, per_page: 100 }),
            contactService.fetchList({ page: 1, per_page: 100 }),
            volunteerService.fetchList({ page: 1, per_page: 1 }),
        ]

        if (isSuperAdmin.value) {
            requests.push(donationService.fetchList({ admin: true, page: 1, per_page: 100 }))
            requests.push(paymentService.fetchList({ page: 1, per_page: 5 }))
        }

        const results = await Promise.all(requests)

        const casesRes = results[0]
        const activeCasesRes = results[1]
        const helpRes = results[2]
        const helpAllRes = results[3]
        const messagesRes = results[4]
        const volunteersRes = results[5]

        const caseItems = casesRes.data || []
        recentCases.value = caseItems
            .filter((item) => ['active', 'open', 'in_progress'].includes(String(item.status || '').toLowerCase()))
            .slice(0, 5)
        stats.value.totalCases = casesRes.meta?.total ?? caseItems.length
        stats.value.activeCases = activeCasesRes.meta?.total ?? 0

        recentHelp.value = helpRes.data || []
        const helpAllItems = helpAllRes.data || []
        stats.value.pendingHelp = helpAllItems.filter((item) =>
            ['pending', 'new'].includes(String(item.status || '').toLowerCase())
        ).length

        const messageItems = messagesRes.data || []
        stats.value.newMessages = messageItems.filter((item) =>
            String(item.status || '').toLowerCase() === 'new'
        ).length

        stats.value.volunteers = volunteersRes.meta?.total ?? (volunteersRes.data?.length || 0)

        if (isSuperAdmin.value) {
            const donationsRes = results[6]
            const paymentsRes = results[7]

            stats.value.totalDonations = (donationsRes.data || []).reduce(
                (sum, row) => sum + Number(row.amount || 0),
                0
            )

            recentPayments.value = paymentsRes.data || []
            stats.value.onlinePayments = paymentsRes.meta?.total ?? recentPayments.value.length
        }
    } catch (error) {
        console.error('Dashboard load error:', error)
        loadError.value = true
    } finally {
        loading.value = false
    }
}

const loadBlog = async () => {
    try {
        const res = await blogService.getAll()
        posts.value = Array.isArray(res?.data) ? res.data : Array.isArray(res) ? res : []
    } catch {
        posts.value = []
    }
}

onMounted(async () => {
    if (props.activeTab === 'overview') {
        await loadOverview()
        return
    }

    if (props.activeTab === 'blog') {
        await loadBlog()
    }
})
</script>

<template>
    <div class="space-y-8">
        <!-- OVERVIEW -->
        <template v-if="props.activeTab === 'overview'">
            <section class="relative overflow-hidden rounded-3xl border border-[#2A7DE1]/15 bg-gradient-to-br from-[#2A7DE1] via-[#2569c7] to-[#1a4f9c] p-6 md:p-8 text-white shadow-lg shadow-[#2A7DE1]/20">
                <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10 blur-2xl" />
                <div class="absolute -bottom-10 left-1/3 h-32 w-32 rounded-full bg-white/10 blur-xl" />

                <div class="relative flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">Mexrli Insonlar — boshqaruv paneli</p>
                        <h1 class="mt-1 text-2xl font-bold md:text-3xl">{{ t('admin.dashboard') }}</h1>
                        <p class="mt-2 max-w-xl text-sm text-white/85">
                            Holatlar, xayriyalar va murojaatlarni bir joydan kuzating. Quyida eng muhim ko‘rsatkichlar.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 rounded-2xl bg-white/15 px-4 py-3 text-sm backdrop-blur-sm">
                        <TrendingUp class="h-5 w-5 shrink-0" />
                        <span>{{ new Date().toLocaleDateString('uz-UZ', { weekday: 'long', day: 'numeric', month: 'long' }) }}</span>
                    </div>
                </div>
            </section>

            <div v-if="loading" class="rounded-2xl border border-gray-100 bg-white p-8 text-center text-gray-500">
                Ma’lumotlar yuklanmoqda...
            </div>

            <div
                v-else-if="loadError"
                class="flex items-center gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700"
            >
                <AlertCircle class="h-5 w-5 shrink-0" />
                Dashboard ma’lumotlarini yuklab bo‘lmadi. Sahifani yangilang.
            </div>

            <template v-else>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <router-link
                        v-for="card in statCards"
                        :key="card.key"
                        :to="card.to"
                        class="group rounded-2xl border bg-gradient-to-br p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                        :class="toneClasses[card.tone]"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/80 shadow-sm"
                            >
                                <component :is="card.icon" class="h-5 w-5" />
                            </div>
                            <ArrowRight class="h-4 w-4 opacity-0 transition group-hover:opacity-100" />
                        </div>
                        <p class="mt-4 text-2xl font-bold leading-none">
                            {{ card.isMoney ? card.value : formatAmount(card.value) }}
                        </p>
                        <p class="mt-2 text-sm font-semibold">{{ card.label }}</p>
                        <p class="mt-1 text-xs opacity-80">{{ card.hint }}</p>
                    </router-link>
                </div>

                <section>
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Tezkor havolalar</h2>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <router-link
                            v-for="link in quickLinks"
                            :key="link.to"
                            :to="link.to"
                            class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-[#2A7DE1]/30 hover:shadow-md"
                        >
                            <p class="font-semibold text-gray-900">{{ link.label }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ link.desc }}</p>
                        </router-link>
                    </div>
                </section>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <h2 class="font-bold text-gray-900">So‘nggi yordam so‘rovlari</h2>
                            <router-link to="/admin/help-requests" class="text-sm font-medium text-[#2A7DE1] hover:underline">
                                Barchasi
                            </router-link>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <div
                                v-for="item in recentHelp"
                                :key="item.id"
                                class="flex items-center justify-between gap-3 px-5 py-4"
                            >
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-900">{{ item.full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ item.city || '—' }} · {{ formatDate(item.created_at) }}</p>
                                </div>
                                <StatusBadge :status="item.status" :map="HELP_REQUEST_STATUS" />
                            </div>
                            <p v-if="recentHelp.length === 0" class="px-5 py-8 text-center text-sm text-gray-500">
                                So‘rovlar yo‘q
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <h2 class="font-bold text-gray-900">Faol holatlar</h2>
                            <router-link to="/admin/cases" class="text-sm font-medium text-[#2A7DE1] hover:underline">
                                Barchasi
                            </router-link>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <div
                                v-for="item in recentCases"
                                :key="item.id"
                                class="px-5 py-4"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate font-medium text-gray-900">{{ item.name || item.title }}</p>
                                    <span class="text-xs font-semibold text-[#2A7DE1]">
                                        {{ progressPercent(item.raised_amount, item.goal_amount) }}%
                                    </span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-[#2A7DE1] to-[#4CAF50] transition-all"
                                        :style="{ width: `${progressPercent(item.raised_amount, item.goal_amount)}%` }"
                                    />
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    {{ formatMoney(item.raised_amount) }} / {{ formatMoney(item.goal_amount) }}
                                </p>
                            </div>
                            <p v-if="recentCases.length === 0" class="px-5 py-8 text-center text-sm text-gray-500">
                                Faol holatlar yo‘q
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="isSuperAdmin"
                    class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden"
                >
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <h2 class="font-bold text-gray-900">So‘nggi onlayn to‘lovlar</h2>
                        <router-link to="/admin/payments" class="text-sm font-medium text-[#2A7DE1] hover:underline">
                            Barchasi
                        </router-link>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div
                            v-for="payment in recentPayments"
                            :key="payment.id"
                            class="flex items-center justify-between gap-4 px-5 py-4"
                        >
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ providerLabel(payment.provider) }}</p>
                                <p class="truncate text-xs text-gray-500">{{ payment.transaction_id || '—' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-semibold text-gray-900">{{ formatMoney(payment.amount) }}</p>
                                <StatusBadge :status="payment.status" :map="PAYMENT_STATUSES" />
                            </div>
                        </div>
                        <p v-if="recentPayments.length === 0" class="px-5 py-8 text-center text-sm text-gray-500">
                            Onlayn to‘lovlar yo‘q
                        </p>
                    </div>
                </div>
            </template>
        </template>

        <!-- BLOG TAB (legacy route) -->
        <template v-else-if="props.activeTab === 'blog'">
            <h1 class="text-2xl font-bold text-gray-900">{{ t('admin.blog') }}</h1>
            <div class="mt-4 space-y-3">
                <div
                    v-for="post in posts"
                    :key="post.id"
                    class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <p class="font-semibold text-gray-900">{{ post.title }}</p>
                    <p class="text-sm text-gray-500">{{ post.category }}</p>
                </div>
                <p v-if="posts.length === 0" class="text-sm text-gray-500">{{ t('admin.noBlogPosts') }}</p>
            </div>
        </template>
    </div>
</template>
