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
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.orgName') }}
                            </p>
                            <p class="font-semibold text-gray-800">{{ legalItems[0]?.content || '—' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.inn') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">{{ legalItems[1]?.content || '—' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.legalAddress') }}
                            </p>
                            <p class="font-semibold text-gray-800">{{ legalItems[2]?.content || '—' }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.bank') }}
                            </p>
                            <p class="font-semibold text-gray-800">{{ legalItems[3]?.content || '—' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.accountUzs') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">{{ legalItems[4]?.content || '—' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.mfoBik') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">{{ legalItems[5]?.content || '—' }}</p>
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
import { computed, ref, onMounted } from 'vue'
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

const fetchPage = async () => {
    try {
        const res = await pageService.getBySlug('about')
        page.value = res.data
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}


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

const sections = computed(() => page.value?.sections ?? [])
const byType = (type) => sections.value.filter((item) => item.type === type)

const hero = computed(() => byType('hero')[0] ?? null)

const values = computed(() => {
    const items = byType('value')
    if (items.length) {
        return items.map((item) => ({
            icon: iconMap[item.extra?.icon] || ShieldCheck,
            title: item.title,
            desc: item.content,
            tone: item.extra?.tone || 'blue',
        }))
    }

    return [
        { icon: ShieldCheck, title: tm('aboutPage.values')[0].title, desc: tm('aboutPage.values')[0].desc, tone: 'blue' },
        { icon: Heart, title: tm('aboutPage.values')[1].title, desc: tm('aboutPage.values')[1].desc, tone: 'red' },
        { icon: Target, title: tm('aboutPage.values')[2].title, desc: tm('aboutPage.values')[2].desc, tone: 'green' },
        { icon: Star, title: tm('aboutPage.values')[3].title, desc: tm('aboutPage.values')[3].desc, tone: 'orange' },
    ]
})

const docs = computed(() => {
    const items = byType('doc')
    if (items.length) {
        return items.map((item) => ({
            title: item.title,
            desc: item.content,
            href: item.extra?.href || '#',
            tone: item.extra?.tone || 'blue',
            icon: iconMap[item.extra?.icon] || FileText,
        }))
    }

    return [
        { title: tm('aboutPage.docs')[0].title, desc: tm('aboutPage.docs')[0].desc, href: '#', tone: 'blue', icon: FileText },
        { title: tm('aboutPage.docs')[1].title, desc: tm('aboutPage.docs')[1].desc, href: '#', tone: 'green', icon: ScrollText },
    ]
})

const legalItems = computed(() => byType('legal'))

const team = computed(() => {
    const items = byType('team')
    if (items.length) {
        return items.map((item) => ({
            name: item.title,
            role: item.subtitle || item.content || '',
            initials: (item.title || '?').split(' ').slice(0, 2).map((v) => v[0]).join('').toUpperCase(),
        }))
    }

    return [
        { name: 'Dr. Aziza Karimova', role: tm('aboutPage.team')[0].role, initials: 'AK' },
        { name: 'Rustam Alimov', role: tm('aboutPage.team')[1].role, initials: 'RA' },
        { name: 'Nodira Tosheva', role: tm('aboutPage.team')[2].role, initials: 'NT' },
        { name: 'Bekzod Umarov', role: tm('aboutPage.team')[3].role, initials: 'BU' },
    ]
})

onMounted(fetchPage)
</script>
