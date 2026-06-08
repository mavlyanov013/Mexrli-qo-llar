<template>
    <div class="pt-24 pb-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <RouterLink
                to="/cases"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#2A7DE1] mb-6 transition-colors"
            >
                <span>←</span>
                {{ t('caseDetailPage.backToCases') }}
            </RouterLink>

            <div v-if="loading" class="animate-pulse space-y-6">
                <div class="h-8 bg-gray-200 rounded w-1/3" />
                <div class="h-96 bg-gray-200 rounded-2xl" />
            </div>

            <template v-else-if="caseData">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="rounded-2xl overflow-hidden space-y-3">
                            <img
                                :src="activePhoto"
                                :alt="content(caseData, 'name')"
                                class="w-full aspect-video object-cover"
                            />

                            <div v-if="photoItems.length > 1" class="flex gap-2 overflow-x-auto pb-1">
                                <button
                                    v-for="(photo, i) in photoItems"
                                    :key="i"
                                    type="button"
                                    class="shrink-0 rounded-lg overflow-hidden border-2 transition"
                                    :class="i === activePhotoIndex ? 'border-[#2A7DE1]' : 'border-transparent opacity-70 hover:opacity-100'"
                                    @click="activePhotoIndex = i"
                                >
                                    <img
                                        :src="photo.url"
                                        :alt="photo.name"
                                        class="w-20 h-14 object-cover"
                                    />
                                </button>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900">
                                        {{ content(caseData, 'name') }}
                                    </h1>

                                    <div class="flex items-center gap-3 mt-2 flex-wrap">
                                        <span
                                            v-if="content(caseData, 'location')"
                                            class="text-sm text-gray-500 flex items-center gap-1"
                                        >
                                            <MapPin class="w-3.5 h-3.5 text-red-400 shrink-0" />
                                            {{ content(caseData, 'location') }}
                                        </span>

                                        <span
                                            v-if="caseData.age"
                                            class="text-sm text-gray-500"
                                        >
                                            {{ t('caseDetailPage.age', { age: caseData.age }) }}
                                        </span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl hover:bg-gray-100"
                                    @click="shareCase"
                                >
                                    <Share2 class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="inline-flex items-center gap-1.5 bg-green-50 text-[#4CAF50] px-3 py-1 rounded-full text-xs font-semibold border border-green-100">
                                    {{ t('caseDetailPage.verifiedCase') }}
                                </span>

                                <span
                                    v-if="medicalDocuments.length > 0"
                                    class="inline-flex items-center gap-1.5 bg-blue-50 text-[#2A7DE1] px-3 py-1 rounded-full text-xs font-semibold border border-blue-100"
                                >
                                    {{ t('caseDetailPage.documentsChecked') }}
                                </span>

                                <span
                                    v-if="donations.length > 0"
                                    class="inline-flex items-center gap-1.5 bg-orange-50 text-[#FF9800] px-3 py-1 rounded-full text-xs font-semibold border border-orange-100"
                                >
                                    {{ t('caseDetailPage.supporters', { count: donations.length }) }}
                                </span>
                            </div>

                            <span
                                v-if="content(caseData, 'condition')"
                                class="inline-flex bg-blue-50 text-[#2A7DE1] border-0 mb-4 px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ content(caseData, 'condition') }}
                            </span>

                            <div class="prose prose-gray max-w-none">
                                <p class="text-gray-600 leading-relaxed whitespace-pre-wrap">
                                    {{ content(caseData, 'story') || content(caseData, 'short_description') }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 border border-gray-100">
                            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                                <IconBadge :icon="Wallet" tone="orange" size="sm" />
                                {{ t('caseDetailPage.expenseBreakdown') }}
                            </h3>

                            <div class="divide-y divide-gray-50">
                                <div class="flex justify-between py-2.5">
                                    <span class="text-sm text-gray-500">{{ t('caseDetailPage.childName') }}</span>
                                    <span class="text-sm font-semibold">{{ content(caseData, 'name') }}</span>
                                </div>

                                <div
                                    v-if="content(caseData, 'condition')"
                                    class="flex justify-between py-2.5"
                                >
                                    <span class="text-sm text-gray-500">{{ t('caseDetailPage.diagnosis') }}</span>
                                    <span class="text-sm font-semibold text-right max-w-[60%]">
                                        {{ content(caseData, 'condition') }}
                                    </span>
                                </div>

                                <div
                                    v-if="content(caseData, 'location')"
                                    class="flex justify-between py-2.5"
                                >
                                    <span class="text-sm text-gray-500">{{ t('caseDetailPage.region') }}</span>
                                    <span class="text-sm font-semibold">{{ content(caseData, 'location') }}</span>
                                </div>

                                <div class="flex justify-between py-2.5">
                                    <span class="text-sm text-gray-500">{{ t('caseDetailPage.goalAmount') }}</span>
                                    <span class="text-sm font-semibold text-[#4CAF50]">
                                        {{ formatMoney(caseData.goal_amount) }}
                                    </span>
                                </div>

                                <div class="flex justify-between py-2.5">
                                    <span class="text-sm text-gray-500">{{ t('caseDetailPage.raisedAmount') }}</span>
                                    <span class="text-sm font-semibold text-[#2A7DE1]">
                                        {{ formatMoney(caseData.raised_amount) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 border border-gray-100">
                            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                                <IconBadge :icon="FileText" tone="blue" size="sm" />
                                {{ t('caseDetailPage.supportingDocuments') }}
                            </h3>

                            <div v-if="medicalDocuments.length === 0" class="text-sm text-gray-400">
                                {{ t('caseDetailPage.documentsLater') }}
                            </div>

                            <div v-else class="space-y-2">
                                <a
                                    v-for="(doc, index) in medicalDocuments"
                                    :key="index"
                                    :href="resolveDocumentUrl(doc)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors group border border-gray-100"
                                >
                                    <div class="flex items-center gap-3">
                                        <IconBadge :icon="FileText" tone="red" size="xs" />

                                        <div>
                                            <p class="text-sm font-medium text-gray-800">
                                                {{ t('caseDetailPage.medicalDocument', { index: index + 1 }) }}
                                            </p>
                                        </div>
                                    </div>

                                    <Download class="w-4 h-4 text-gray-300 group-hover:text-[#2A7DE1]" />
                                </a>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 border border-gray-100">
                            <h3 class="font-bold text-lg mb-5 flex items-center gap-2">
                                <IconBadge :icon="Heart" tone="red" size="sm" />
                                {{ t('caseDetailPage.treatmentJourney') }}
                            </h3>

                            <div v-if="treatmentProcesses.length === 0 && sortedUpdates.length === 0" class="text-sm text-gray-400">
                                {{ t('caseDetailPage.updatesWillAppear') }}
                            </div>

                            <div v-else-if="treatmentProcesses.length > 0" class="relative">
                                <div class="absolute left-3.5 top-0 bottom-0 w-0.5 bg-gray-100" />

                                <div class="space-y-6">
                                    <div
                                        v-for="(item, index) in treatmentProcesses"
                                        :key="item.id"
                                        class="flex gap-4 relative"
                                    >
                                        <div class="w-7 h-7 rounded-full bg-[#2A7DE1] flex items-center justify-center shrink-0 z-10 text-white text-xs font-bold">
                                            {{ treatmentProcesses.length - index }}
                                        </div>

                                        <div class="flex-1 bg-gray-50 rounded-xl p-4">
                                            <p class="font-semibold text-gray-800">
                                                {{ content(item, 'title') }}
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1 whitespace-pre-wrap">
                                                {{ content(item, 'description') }}
                                            </p>

                                            <div
                                                v-if="item.images?.length"
                                                class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2"
                                            >
                                                <img
                                                    v-for="(image, imageIndex) in item.images"
                                                    :key="`${item.id}-${imageIndex}`"
                                                    :src="image"
                                                    :alt="content(item, 'title')"
                                                    class="rounded-lg w-full max-h-56 object-cover"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="relative">
                                <div class="absolute left-3.5 top-0 bottom-0 w-0.5 bg-gray-100" />

                                <div class="space-y-6">
                                    <div
                                        v-for="(item, index) in sortedUpdates"
                                        :key="index"
                                        class="flex gap-4 relative"
                                    >
                                        <div class="w-7 h-7 rounded-full bg-[#2A7DE1] flex items-center justify-center shrink-0 z-10 text-white text-xs font-bold">
                                            {{ sortedUpdates.length - index }}
                                        </div>

                                        <div class="flex-1 bg-gray-50 rounded-xl p-4">
                                            <p class="text-xs text-gray-400 mb-1">
                                                {{ item.date }}
                                            </p>
                                            <p class="font-semibold text-gray-800">
                                                {{ content(item, 'title') }}
                                            </p>
                                            <p
                                                v-if="item.content"
                                                class="text-sm text-gray-600 mt-1"
                                            >
                                                {{ content(item, 'content') }}
                                            </p>
                                            <img
                                                v-if="item.image_url"
                                                :src="item.image_url"
                                                :alt="content(item, 'title')"
                                                class="mt-3 rounded-lg w-full max-h-56 object-cover"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="relatedNews.length > 0"
                            class="bg-white rounded-2xl p-6 border border-gray-100"
                        >
                            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                                📰 {{ t('caseDetailPage.relatedNews') }}
                            </h3>

                            <div class="space-y-3">
                                <RouterLink
                                    v-for="post in relatedNews"
                                    :key="post.id"
                                    :to="`/news/${post.slug || post.id}`"
                                    class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group border border-gray-100"
                                >
                                    <img
                                        v-if="post.cover_image"
                                        :src="post.cover_image"
                                        :alt="content(post, 'title')"
                                        class="w-14 h-14 rounded-lg object-cover shrink-0"
                                    />

                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 group-hover:text-[#2A7DE1] transition-colors line-clamp-2">
                                            {{ content(post, 'title') }}
                                        </p>

                                        <p
                                            v-if="post.created_at || post.created_date"
                                            class="text-xs text-gray-400 mt-1"
                                        >
                                            {{ formatShortDate(post.created_at || post.created_date) }}
                                        </p>
                                    </div>
                                </RouterLink>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 sticky top-24">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-gray-500">{{ t('caseDetailPage.raised') }}</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ formatMoney(caseData.raised_amount || 0) }}
                                    </p>
                                </div>

                                <ProgressRing
                                    :percentage="percentage"
                                    :size="72"
                                    :stroke-width="6"
                                />
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-500">{{ t('caseDetailPage.funded', { percent: percentage }) }}</span>
                                    <span class="font-medium">
                                        {{ t('caseDetailPage.goal', { amount: formatMoney(caseData.goal_amount) }) }}
                                    </span>
                                </div>

                                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-1000"
                                        :style="progressStyle"
                                    />
                                </div>
                            </div>

                            <RouterLink
                                :to="`/donate?caseId=${caseData.id}`"
                                class="w-full inline-flex items-center justify-center h-12 bg-[#FF9800] hover:bg-[#F57C00] text-white rounded-xl gap-2 text-base font-semibold shadow-lg shadow-orange-200/50"
                            >
                                <IconBadge :icon="Heart" tone="red" size="xs" class="shrink-0" />
                                {{ t('caseDetailPage.donateNow') }}
                            </RouterLink>

                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                    {{ t('caseDetailPage.fundingBreakdown') }}
                                </h4>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ t('caseDetailPage.required') }}</span>
                                        <span class="font-semibold">
                                            {{ formatMoney(caseData.goal_amount) }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ t('caseDetailPage.raisedShort') }}</span>
                                        <span class="font-semibold text-[#4CAF50]">
                                            {{ formatMoney(caseData.raised_amount || 0) }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ t('caseDetailPage.remaining') }}</span>
                                        <span class="font-semibold text-[#FF9800]">
                                            {{ formatMoney(remainingAmount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="donations.length > 0"
                                class="mt-6 pt-6 border-t border-gray-100"
                            >
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                    {{ t('caseDetailPage.recentDonors', { count: donations.length }) }}
                                </h4>

                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                    <div
                                        v-for="donation in donations"
                                        :key="donation.id"
                                        class="flex items-center justify-between"
                                    >
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-xs font-bold text-[#2A7DE1]">
                                                {{ donation.is_anonymous ? '?' : donorInitial(donation) }}
                                            </div>

                                            <span class="text-sm text-gray-600">
                                                {{ donation.is_anonymous ? t('caseDetailPage.anonymous') : donation.donor_name }}
                                            </span>
                                        </div>

                                        <span class="text-sm font-semibold">
                                            {{ formatMoney(donation.amount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div v-else class="text-center py-16 text-gray-500">
                {{ t('caseDetailPage.notFound') }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLocalizedDisplay } from '@/composables/useLocalizedDisplay'
import { RouterLink, useRoute } from 'vue-router'
import caseService from '../services/caseService'
import donationService from '../services/donationService'
import newsService from '../services/newsService'
import treatmentProcessService from '../services/treatmentProcessService'
import { Download, FileText, Heart, MapPin, Share2, Wallet } from 'lucide-vue-next'
import IconBadge from '../components/shared/IconBadge.vue'
import ProgressRing from '../components/shared/ProgressRing.vue'
import { formatAmount } from '@/utils/formatAmount'
import { getCasePhotoItems } from '@/utils/casePhotos'

const { t, locale } = useI18n()
const { content } = useLocalizedDisplay()
const route = useRoute()

const loading = ref(false)
const caseData = ref(null)
const donations = ref([])
const relatedNews = ref([])
const treatmentProcesses = ref([])
const activePhotoIndex = ref(0)

const fetchCase = async () => {
    loading.value = true

    try {
        const result = await caseService.getCaseById(route.params.id, { admin: false })
        caseData.value = result.data || null
        activePhotoIndex.value = 0

        if (caseData.value?.id) {
            await Promise.all([fetchDonations(), fetchRelatedNews(), fetchTreatmentProcesses()])
        }
    } catch (error) {
        console.error('Case detail load error:', error)
        caseData.value = null
    } finally {
        loading.value = false
    }
}

const fetchDonations = async () => {
    try {
        const allDonations = await donationService.getCompletedDonations()
        donations.value = allDonations.filter(
            (item) =>
                String(item.case_id) === String(caseData.value.id) &&
                item.status === 'completed'
        )
    } catch (error) {
        console.error('Case donations load error:', error)
        donations.value = []
    }
}

const fetchTreatmentProcesses = async () => {
    try {
        treatmentProcesses.value = await treatmentProcessService.getPublicByCase(caseData.value.id)
    } catch (error) {
        console.error('Treatment processes load error:', error)
        treatmentProcesses.value = []
    }
}

const fetchRelatedNews = async () => {
    try {
        const { data: allPosts } = await newsService.getLatest({ per_page: 20 })
        relatedNews.value = allPosts.filter(
            (item) => String(item.case_id) === String(caseData.value.id)
        ).slice(0, 5)
    } catch (error) {
        console.error('Related news load error:', error)
        relatedNews.value = []
    }
}

const photoItems = computed(() => getCasePhotoItems(caseData.value))

const activePhoto = computed(() => {
    return photoItems.value[activePhotoIndex.value]?.url || '/placeholder.jpg'
})

const percentage = computed(() => {
    const goal = Number(caseData.value?.goal_amount || 0)
    const raised = Number(caseData.value?.raised_amount || 0)

    if (goal <= 0) return 0
    return Math.min(Math.round((raised / goal) * 100), 100)
})

const remainingAmount = computed(() => {
    const goal = Number(caseData.value?.goal_amount || 0)
    const raised = Number(caseData.value?.raised_amount || 0)
    return Math.max(goal - raised, 0)
})

const medicalDocuments = computed(() => {
    const docs = caseData.value?.medical_documents || []
    return Array.isArray(docs) ? docs : []
})

const sortedUpdates = computed(() => {
    const updates = caseData.value?.updates || []
    if (!Array.isArray(updates)) return []

    return [...updates].sort((a, b) => (b.date || '').localeCompare(a.date || ''))
})

const progressStyle = computed(() => {
    let background = '#FF9800'

    if (percentage.value >= 80) background = '#4CAF50'
    else if (percentage.value >= 50) background = '#2A7DE1'

    return {
        width: `${percentage.value}%`,
        background,
    }
})


const formatMoney = (value) => formatAmount(value)
const formatShortDate = (value) => {
    if (!value) return ''

    const localeMap = {
        uz: 'uz-UZ',
        uz_cyrl: 'uz-UZ',
        ru: 'ru-RU',
    }

    return new Date(value).toLocaleDateString(localeMap[locale.value] || 'uz-UZ', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

const donorInitial = (donation) => {
    return donation?.donor_name?.[0] || '?'
}

const resolveDocumentUrl = (doc) => {
    if (typeof doc === 'string') return doc
    return doc?.url || '#'
}

const shareCase = async () => {
    try {
        if (navigator.share && caseData.value) {
            await navigator.share({
                url: window.location.href,
                title: content(caseData.value, 'name'),
            })
        }
    } catch (error) {
        console.error('Share failed:', error)
    }
}

onMounted(fetchCase)
</script>
