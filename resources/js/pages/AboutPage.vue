<template>
    <div class="pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-5">
                    {{ t('aboutPage.title') }}
                </h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    {{ t('aboutPage.subtitle') }}
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
                    v-for="(item, index) in staticValues"
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

            <SectionHeader
                v-if="docs.length"
                :title="t('aboutPage.docsTitle')"
                :subtitle="t('aboutPage.docsSubtitle')"
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-20">
                <component
                    :is="doc.file_url ? 'a' : 'div'"
                    v-for="doc in docs"
                    :key="doc.key"
                    :href="doc.file_url || undefined"
                    :download="doc.file_url ? '' : undefined"
                    :target="doc.file_url ? '_blank' : undefined"
                    class="flex items-center gap-5 bg-white rounded-2xl p-6 shadow-sm transition group"
                    :class="doc.file_url ? 'cursor-pointer hover:shadow-md' : ''"
                >
                    <IconBadge
                        :icon="doc.icon"
                        :tone="doc.tone"
                        size="lg"
                    />

                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 group-hover:text-[#2A7DE1] transition-colors">
                            {{ content(doc, 'title') }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">{{ content(doc, 'description') }}</p>
                    </div>

                    <ExternalLink v-if="doc.file_url" class="w-4 h-4 text-gray-300 group-hover:text-[#2A7DE1]" />
                </component>
            </div>

            <!-- Bank rekvizitlari (API) -->
            <div class="bg-white rounded-2xl p-8 border border-gray-100 mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <IconBadge :icon="Building2" tone="blue" size="md" />
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ t('aboutPage.bankTitle') }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ t('aboutPage.bankSubtitle') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                            {{ t('aboutPage.bank') }}
                        </p>
                        <p class="font-semibold text-gray-800">
                            {{ content(bank, 'bank') || '—' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                            {{ t('aboutPage.accountUzs') }}
                        </p>
                        <p class="font-semibold text-gray-800 font-mono">
                            {{ bank.account_uzs || '—' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 md:col-span-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                            {{ t('aboutPage.mfoBik') }}
                        </p>
                        <p class="font-semibold text-gray-800 font-mono">
                            {{ bank.mfo_bik || '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Yuridik ma'lumotlar (API) -->
            <div class="bg-white rounded-2xl p-8 border border-gray-100 mb-20">
                <div class="flex items-center gap-3 mb-6">
                    <IconBadge :icon="FileText" tone="green" size="md" />
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ t('aboutPage.legalInfoTitle') }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ t('aboutPage.legalSubtitle') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-xl p-4 md:col-span-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                            {{ t('aboutPage.orgName') }}
                        </p>
                        <p class="font-semibold text-gray-800">
                            {{ content(legal, 'org_name') || '—' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                            {{ t('aboutPage.inn') }}
                        </p>
                        <p class="font-semibold text-gray-800 font-mono">
                            {{ legal.inn || '—' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 md:col-span-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                            {{ t('aboutPage.legalAddress') }}
                        </p>
                        <p class="font-semibold text-gray-800">
                            {{ content(legal, 'legal_address') || '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <SectionHeader
                :title="t('aboutPage.teamTitle')"
                :subtitle="t('aboutPage.teamSubtitle')"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="member in team"
                    :key="member.id"
                    class="bg-white rounded-2xl p-6 border border-gray-100 text-center"
                >
                    <img
                        v-if="member.photo_url"
                        :src="member.photo_url"
                        :alt="member.name"
                        class="w-20 h-20 mx-auto mb-4 rounded-full object-cover"
                    />
                    <div
                        v-else
                        class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-[#2A7DE1] to-[#1E5BB8] flex items-center justify-center text-xl font-bold text-white"
                    >
                        {{ member.initials }}
                    </div>
                    <h3 class="font-bold text-gray-900">{{ content(member, 'name') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ content(member, 'position') || '—' }}</p>
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
import aboutService from '@/services/aboutService'
import { useLocalizedDisplay } from '@/composables/useLocalizedDisplay'

const { t, tm } = useI18n()
const { content } = useLocalizedDisplay()

const docs = ref([])

const bank = ref({
    bank: '',
    account_uzs: '',
    mfo_bik: '',
})

const legal = ref({
    org_name: '',
    inn: '',
    legal_address: '',
})

const team = ref([])

const valueIcons = [ShieldCheck, Heart, Target, Star]
const valueTones = ['blue', 'red', 'green', 'yellow']
const docMeta = {
    registration_certificate: { icon: FileText, tone: 'blue' },
    organization_charter: { icon: ScrollText, tone: 'green' },
}

const staticValues = computed(() => {
    const items = tm('aboutPage.values') || []

    return items.map((item, index) => ({
        icon: valueIcons[index] || ShieldCheck,
        tone: valueTones[index] || 'blue',
        title: item.title,
        desc: item.desc,
    }))
})

const initials = (name) => {
    return String(name || '')
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase() || 'T'
}

const fetchContent = async () => {
    const data = await aboutService.getContent()

    bank.value = data.bank ?? bank.value
    legal.value = data.legal ?? legal.value

    docs.value = (data.docs ?? []).map((doc) => ({
        ...doc,
        icon: docMeta[doc.key]?.icon || FileText,
        tone: docMeta[doc.key]?.tone || 'blue',
    }))

    team.value = (data.team ?? []).map((member) => ({
        ...member,
        initials: initials(member.name),
    }))
}

onMounted(fetchContent)
</script>
