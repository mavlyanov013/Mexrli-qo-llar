<template>
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div>
                    <div class="relative">
                        <div class="w-64 h-64 mx-auto relative">
                            <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                <circle
                                    v-for="(seg, i) in segments"
                                    :key="i"
                                    cx="50"
                                    cy="50"
                                    r="40"
                                    fill="none"
                                    :stroke="seg.color"
                                    stroke-width="12"
                                    :stroke-dasharray="getDashArray(seg.pct)"
                                    :stroke-dashoffset="getDashOffset(i)"
                                    class="transition-all duration-1000"
                                />
                            </svg>

                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-green-500 text-xl mb-1">✔</div>
                                    <span class="text-2xl font-bold text-gray-900">92%</span>
                                    <p class="text-xs text-gray-500">{{ t('transparency.directAid') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-center gap-4 mt-6">
                            <div
                                v-for="(seg, i) in segments"
                                :key="i"
                                class="flex items-center gap-2"
                            >
                                <div
                                    class="w-3 h-3 rounded-full"
                                    :style="{ background: seg.color }"
                                />
                                <span class="text-sm text-gray-600">
                                    {{ seg.label }} ({{ seg.pct }}%)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="inline-flex items-center gap-2 bg-green-50 text-[#4CAF50] px-4 py-2 rounded-full text-sm font-medium mb-5">
                        📊 {{ t('transparency.badge') }}
                    </div>

                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-5">
                        {{ t('transparency.title') }}
                    </h2>

                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        {{ t('transparency.subtitle') }}
                    </p>

                    <router-link to="/transparency">
                        <button class="rounded-xl gap-2 font-semibold border-2 px-6 py-3 inline-flex items-center">
                            {{ t('transparency.viewReports') }} →
                        </button>
                    </router-link>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const segments = computed(() => [
    { label: t('transparency.medicalAid'), pct: 72, color: '#2A7DE1' },
    { label: t('transparency.rehabilitation'), pct: 12, color: '#4CAF50' },
    { label: t('transparency.familySupport'), pct: 8, color: '#FF9800' },
    { label: t('transparency.operations'), pct: 8, color: '#94A3B8' },
])

const totalCircumference = 251

const getDashArray = (pct) => {
    return `${pct * 2.51} ${totalCircumference - pct * 2.51}`
}

const getDashOffset = (index) => {
    let offset = 0
    for (let i = 0; i < index; i++) {
        offset += segments.value[i].pct
    }
    return -offset * 2.51
}
</script>
