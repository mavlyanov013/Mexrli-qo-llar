<template>
    <section class="py-24 bg-gradient-to-br from-[#2A7DE1] to-[#1E5BB8] relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl" />
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-white rounded-full blur-3xl" />
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-white/20 flex items-center justify-center">
                <span class="w-8 h-8 text-white text-2xl">❤</span>
            </div>

            <h2 class="text-3xl md:text-5xl font-bold text-white mb-5">
                {{ t('finalCta.titleLine1') }}<br />
                {{ t('finalCta.titleLine2') }}
            </h2>

            <p class="text-lg text-white/80 mb-3 max-w-lg mx-auto">
                {{ t('finalCta.subtitle') }}
            </p>

            <div class="flex items-center justify-center gap-6 text-white/60 text-sm mb-10 flex-wrap">
                <span>✓ {{ t('finalCta.verifiedCases') }}</span>
                <span>✓ {{ t('finalCta.financialTransparency') }}</span>
                <span>✓ {{ t('finalCta.realTimeTracking') }}</span>
            </div>

            <router-link to="/donate">
                <button class="bg-[#FF9800] hover:bg-[#F57C00] text-white rounded-xl gap-2 px-10 h-14 text-lg font-semibold shadow-xl shadow-black/20 inline-flex items-center justify-center">
                    {{ t('finalCta.donateNow') }}
                </button>
            </router-link>
        </div>
    </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>
