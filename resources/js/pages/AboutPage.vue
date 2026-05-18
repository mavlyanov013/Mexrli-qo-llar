<template>
    <div class="pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-5">
                    {{ hero?.title || t('aboutPage.title') }}
                </h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    {{ hero?.content || t('aboutPage.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
                <div class="bg-white rounded-2xl p-8 border border-gray-100">
                    <IconBadge :icon="Heart" tone="red" size="lg" class="mb-5" />
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ t('aboutPage.missionTitle') }}</h2>
                    <p class="text-gray-600 leading-relaxed">
                        {{ t('aboutPage.missionText') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-8 border border-gray-100">
                    <IconBadge :icon="Eye" tone="green" size="lg" class="mb-5" />
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ t('aboutPage.visionTitle') }}</h2>
                    <p class="text-gray-600 leading-relaxed">
                        {{ t('aboutPage.visionText') }}
                    </p>
                </div>
            </div>

            <SectionHeader :title="t('aboutPage.valuesTitle')" />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-20">
                <div
                    v-for="(item, index) in values"
                    :key="index"
                    class="bg-white rounded-2xl p-6 border border-gray-100 text-center"
                >
                    <IconBadge
                        :icon="item.icon"
                        :tone="item.tone"
                        size="lg"
                        class="mx-auto mb-4"
                    />
                    <h3 class="font-bold text-gray-900 mb-2">{{ item.title }}</h3>
                    <p class="text-sm text-gray-500">{{ item.desc }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-20">
                <a
                    v-for="(doc, index) in docs"
                    :key="index"
                    :href="doc.href"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-5 bg-white rounded-2xl p-6 border border-gray-100 hover:border-[#2A7DE1]/30 hover:shadow-md transition-all group"
                >
                    <IconBadge
                        :icon="doc.icon"
                        :tone="doc.tone"
                        size="lg"
                    />

                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 group-hover:text-[#2A7DE1] transition-colors">
                            {{ doc.title }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">{{ doc.desc }}</p>
                    </div>

                    <ExternalLink class="w-4 h-4 text-gray-300 group-hover:text-[#2A7DE1]" />
                </a>
            </div>

            <div class="bg-white rounded-2xl p-8 border border-gray-100 mb-20">
                <div class="flex items-center gap-3 mb-6">
                    <IconBadge :icon="Building2" tone="blue" size="md" />
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ t('aboutPage.legalTitle') }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ t('aboutPage.legalSubtitle') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- LEFT SIDE -->
                    <div class="space-y-4">

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.orgName') }}
                            </p>
                            <p class="font-semibold text-gray-800">
                                {{ legalMap.orgName || '—' }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.inn') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">
                                {{ legalMap.inn || '—' }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.legalAddress') }}
                            </p>
                            <p class="font-semibold text-gray-800">
                                {{ legalMap.legalAddress || '—' }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.bank') }}
                            </p>
                            <p class="font-semibold text-gray-800">
                                {{ legalMap.bank || '—' }}
                            </p>
                        </div>

                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="space-y-4">

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.accountUzs') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">
                                {{ legalMap.accountUzs || '—' }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.mfoBik') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">
                                {{ legalMap.mfoBik || '—' }}
                            </p>
                        </div>

                    </div>

                </div>
            </div>

            <SectionHeader
                :title="t('aboutPage.teamTitle')"
                :subtitle="t('aboutPage.teamSubtitle')"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="(member, index) in team"
                    :key="index"
                    class="bg-white rounded-2xl p-6 border border-gray-100 text-center"
                >
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-[#2A7DE1] to-[#1E5BB8] flex items-center justify-center text-xl font-bold text-white">
                        {{ member.initials }}
                    </div>
                    <h3 class="font-bold text-gray-900">{{ member.name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ member.role }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {computed, ref, onMounted, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import {
    Heart,
    Eye,
    ShieldCheck,
    Target,
    Star,
    FileText,
    ScrollText,
    ExternalLink,
    Building2,
} from 'lucide-vue-next'

import SectionHeader from '../components/shared/SectionHeader.vue'
import IconBadge from '../components/shared/IconBadge.vue'
import pageService from '@/services/pageService'

const page = ref(null)
const loading = ref(true)
const error = ref(null)

const { t, tm } = useI18n()

const iconMap = {
    Heart,
    Eye,
    ShieldCheck,
    Target,
    Star,
    FileText,
    ScrollText,
    Building2,
}

/* ================= FETCH ================= */
const fetchPage = async () => {
    loading.value = true
    try {
        const res = await pageService.getBySlug('about')

        page.value = res.data

    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}

/* ================= SAFE PARSE ================= */
const safeExtra = (extra) => {
    if (!extra) return {}
    if (typeof extra === 'string') {
        try {
            return JSON.parse(extra)
        } catch {
            return {}
        }
    }
    return extra
}

/* ================= SECTIONS ================= */
const sections = computed(() => {
    return page.value?.sections?.length
        ? page.value.sections
        : []
})

const byType = (type) => {
    return sections.value.filter(s => String(s?.type) === type)
}

/* ================= HERO ================= */
const hero = computed(() => byType('hero')[0] ?? null)

/* ================= VALUES ================= */
const values = computed(() => {
    const items = byType('value')

    if (!items.length) {
        return tm('aboutPage.values').map((v) => ({
            icon: ShieldCheck,
            title: v.title,
            desc: v.desc,
            tone: 'blue',
        }))
    }

    return items.map((item) => {
        const extra = safeExtra(item.extra)

        return {
            icon: iconMap[extra.icon] || ShieldCheck,
            title: item.title,
            desc: item.content,
            tone: extra.tone || 'blue',
        }
    })
})

/* ================= DOCS ================= */
const docs = computed(() => {
    const items = byType('doc')

    if (!items.length) {
        return tm('aboutPage.docs').map((d) => ({
            title: d.title,
            desc: d.desc,
            href: '#',
            icon: FileText,
            tone: 'blue',
        }))
    }

    return items.map((item) => {
        const extra = safeExtra(item.extra)

        return {
            title: item.title,
            desc: item.content,
            href: extra.href || '#',
            icon: iconMap[extra.icon] || FileText,
            tone: extra.tone || 'blue',
        }
    })
})

/* ================= LEGAL (FIXED ORDER ISSUE) ================= */
const legalMap = computed(() => {
    const map = {}
    byType('legal').forEach((i) => {
        map[i.title] = i.content
    })
    return map
})

/* ================= TEAM ================= */
const team = computed(() => {
    const items = byType('team')

    return items.map((item) => {
        const name = item.title || ''

        const initials = name
            .split(' ')
            .filter(Boolean)
            .map(w => w[0])
            .join('')
            .toUpperCase()

        return {
            name,
            role: item.subtitle || '',
            initials: initials || 'T'
        }
    })
})

onMounted(fetchPage)
</script>
