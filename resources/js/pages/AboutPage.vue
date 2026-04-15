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
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mb-5 text-[#2A7DE1] text-2xl">
                        ❤
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ t('aboutPage.missionTitle') }}</h2>
                    <p class="text-gray-600 leading-relaxed">
                        {{ t('aboutPage.missionText') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-8 border border-gray-100">
                    <div class="w-14 h-14 rounded-2xl bg-green-50 flex items-center justify-center mb-5 text-[#4CAF50] text-2xl">
                        👁
                    </div>
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
                    <div
                        :class="['w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center text-2xl', item.color]"
                    >
                        {{ item.icon }}
                    </div>
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
                    <div
                        :class="['w-14 h-14 rounded-2xl flex-shrink-0 flex items-center justify-center text-2xl', doc.color]"
                    >
                        📄
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 group-hover:text-[#2A7DE1] transition-colors">
                            {{ doc.title }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">{{ doc.desc }}</p>
                    </div>
                    <span class="text-gray-300 group-hover:text-[#2A7DE1]">↗</span>
                </a>
            </div>

            <div class="bg-white rounded-2xl p-8 border border-gray-100 mb-20">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-[#2A7DE1] text-2xl">
                        🏛
                    </div>
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
                            <p class="font-semibold text-gray-800">
                                Благотворительный фонд «Mehrli»
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.inn') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">—</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.legalAddress') }}
                            </p>
                            <p class="font-semibold text-gray-800">—</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.bank') }}
                            </p>
                            <p class="font-semibold text-gray-800">—</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.accountUzs') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">—</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">
                                {{ t('aboutPage.mfoBik') }}
                            </p>
                            <p class="font-semibold text-gray-800 font-mono">—</p>
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
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import SectionHeader from '../components/shared/SectionHeader.vue'

const { t, tm } = useI18n()

const values = computed(() => [
    { icon: '🛡', title: tm('aboutPage.values')[0].title, desc: tm('aboutPage.values')[0].desc, color: 'bg-blue-50 text-[#2A7DE1]' },
    { icon: '❤', title: tm('aboutPage.values')[1].title, desc: tm('aboutPage.values')[1].desc, color: 'bg-red-50 text-red-500' },
    { icon: '🎯', title: tm('aboutPage.values')[2].title, desc: tm('aboutPage.values')[2].desc, color: 'bg-green-50 text-[#4CAF50]' },
    { icon: '⭐', title: tm('aboutPage.values')[3].title, desc: tm('aboutPage.values')[3].desc, color: 'bg-orange-50 text-[#FF9800]' },
])

const docs = computed(() => [
    {
        title: tm('aboutPage.docs')[0].title,
        desc: tm('aboutPage.docs')[0].desc,
        href: '#',
        color: 'bg-blue-50 text-[#2A7DE1]',
    },
    {
        title: tm('aboutPage.docs')[1].title,
        desc: tm('aboutPage.docs')[1].desc,
        href: '#',
        color: 'bg-green-50 text-[#4CAF50]',
    },
])

const team = computed(() => [
    { name: 'Dr. Aziza Karimova', role: tm('aboutPage.team')[0].role, initials: 'AK' },
    { name: 'Rustam Alimov', role: tm('aboutPage.team')[1].role, initials: 'RA' },
    { name: 'Nodira Tosheva', role: tm('aboutPage.team')[2].role, initials: 'NT' },
    { name: 'Bekzod Umarov', role: tm('aboutPage.team')[3].role, initials: 'BU' },
])
</script>
