<template>
    <div class="pt-24 pb-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ t('faqPage.title') }}
                </h1>
                <p class="text-lg text-gray-500">
                    {{ t('faqPage.subtitle') }}
                </p>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(faq, index) in faqs"
                    :key="index"
                    class="bg-white rounded-2xl border border-gray-100 px-6 overflow-hidden"
                >
                    <button
                        type="button"
                        class="w-full text-left font-semibold text-gray-900 py-5 flex items-center justify-between"
                        @click="toggle(index)"
                    >
                        <span>{{ faq.q }}</span>
                        <span class="text-xl text-gray-400">
                            {{ openIndex === index ? '−' : '+' }}
                        </span>
                    </button>

                    <div
                        v-if="openIndex === index"
                        class="text-gray-600 pb-5 leading-relaxed"
                    >
                        {{ faq.a }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, tm } = useI18n()
const openIndex = ref(0)

const faqs = computed(() => tm('faqPage.items'))

const toggle = (index) => {
    openIndex.value = openIndex.value === index ? null : index
}
</script>
