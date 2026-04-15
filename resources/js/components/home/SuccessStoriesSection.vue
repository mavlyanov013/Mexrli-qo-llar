<template>
    <section class="py-20 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <SectionHeader
                :title="t('successStories.title')"
                :subtitle="t('successStories.subtitle')"
            />

            <div class="relative rounded-3xl overflow-hidden group">
                <img
                    :src="successImage"
                    :alt="t('successStories.imageAlt')"
                    class="w-full h-[400px] md:h-[500px] object-cover group-hover:scale-105 transition-transform duration-700"
                />

                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />

                <div class="absolute bottom-0 left-0 right-0 p-8 md:p-12">
                    <div class="w-10 h-10 text-white/40 mb-4 text-4xl">“</div>

                    <p class="text-white text-xl md:text-2xl font-semibold leading-relaxed max-w-2xl">
                        "{{ t('successStories.quote') }}"
                    </p>

                    <router-link to="/news">
                        <button class="mt-6 text-white hover:bg-white/20 rounded-xl gap-2 font-semibold inline-flex items-center justify-center px-4 py-2">
                            {{ t('successStories.readMore') }} →
                        </button>
                    </router-link>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import SectionHeader from '../shared/SectionHeader.vue'

const { t } = useI18n()

defineProps({
    successImage: {
        type: String,
        required: true,
    },
})
</script>
