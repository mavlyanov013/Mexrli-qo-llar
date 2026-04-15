<template>
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <p class="text-sm font-semibold text-[#2A7DE1] uppercase tracking-widest mb-2">
                    {{ t('servicesSection.badge') }}
                </p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    {{ t('servicesSection.title') }}
                </h2>
                <p class="text-gray-500 max-w-2xl mx-auto text-base leading-relaxed">
                    {{ t('servicesSection.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="service in services"
                    :key="service.key"
                    class="group bg-white border border-gray-100 rounded-2xl p-6 flex flex-col transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                    :class="service.border"
                >
                    <div
                        class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5 transition-transform duration-300 group-hover:scale-110"
                        :class="service.color"
                    >
                        <component :is="service.icon" class="w-7 h-7" aria-hidden="true" />
                    </div>

                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">
                        {{ t(`servicesSection.items.${service.key}.subtitle`) }}
                    </p>

                    <h3 class="text-lg font-bold text-gray-900 mb-3 leading-snug">
                        {{ t(`servicesSection.items.${service.key}.title`) }}
                    </h3>

                    <p class="text-sm text-gray-500 leading-relaxed flex-1">
                        {{ t(`servicesSection.items.${service.key}.description`) }}
                    </p>

                    <a
                        :href="service.href"
                        class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-[#2A7DE1] hover:gap-2.5 transition-all duration-200"
                    >
                        {{ t('servicesSection.helpAction') }}
                        <ArrowRight class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import {
    BookOpen,
    Stethoscope,
    Home,
    Wrench,
    ArrowRight,
} from 'lucide-vue-next'

const { t } = useI18n()

const services = [
    {
        key: 'educationalSupport',
        icon: BookOpen,
        color: 'bg-blue-50 text-[#2A7DE1]',
        border: 'hover:border-[#2A7DE1]/30',
        href: '/donate?service=education',
    },
    {
        key: 'surgicalAssistance',
        icon: Stethoscope,
        color: 'bg-red-50 text-red-500',
        border: 'hover:border-red-200',
        href: '/donate?service=surgery',
    },
    {
        key: 'householdSupport',
        icon: Home,
        color: 'bg-green-50 text-[#4CAF50]',
        border: 'hover:border-green-200',
        href: '/donate?service=household',
    },
    {
        key: 'homeRepair',
        icon: Wrench,
        color: 'bg-orange-50 text-[#FF9800]',
        border: 'hover:border-orange-200',
        href: '/donate?service=home_repair',
    },
]
</script>
