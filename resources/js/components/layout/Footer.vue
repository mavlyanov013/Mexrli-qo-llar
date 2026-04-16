<template>
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <div>
                    <div class="flex items-center gap-3 mb-5">
                        <img
                            src="/public/images/logo.png"
                            alt="Mehrli Insonlar"
                            class="w-11 h-11 object-contain rounded-xl"
                        />
                        <span class="text-xl font-bold tracking-tight">Mehrli</span>
                    </div>

                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        {{ t('footer.description') }}
                    </p>

                    <div class="flex gap-3">
                        <a
                            v-for="item in socialLinks"
                            :key="item.key"
                            :href="item.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-lg bg-gray-800 hover:bg-[#2A7DE1] flex items-center justify-center transition-colors"
                            :aria-label="item.label"
                        >
                            <component :is="item.icon" class="w-4 h-4 text-gray-300 hover:text-white" />
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold text-sm uppercase tracking-wider text-gray-400 mb-5">
                        {{ t('footer.quickLinks') }}
                    </h4>

                    <ul class="space-y-3">
                        <li v-for="item in quickLinks" :key="item.to">
                            <RouterLink
                                :to="item.to"
                                class="text-sm text-gray-300 hover:text-white transition-colors"
                            >
                                {{ item.label }}
                            </RouterLink>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-sm uppercase tracking-wider text-gray-400 mb-5">
                        {{ t('footer.contactUs') }}
                    </h4>

                    <ul class="space-y-3">
                        <li class="flex items-center gap-2.5 text-sm text-gray-300">
                            <MapPin class="w-4 h-4 text-gray-400 shrink-0" />
                            {{ t('footer.location') }}
                        </li>

                        <li class="flex items-center gap-2.5 text-sm text-gray-300">
                            <Phone class="w-4 h-4 text-gray-400 shrink-0" />
                            +998 71 123 45 67
                        </li>

                        <li class="flex items-center gap-2.5 text-sm text-gray-300">
                            <Mail class="w-4 h-4 text-gray-400 shrink-0" />
                            info@mehrli.uz
                        </li>
                    </ul>

                    <ul class="space-y-3 mt-6">
                        <li v-for="item in supportLinks" :key="item.to + item.label">
                            <RouterLink
                                :to="item.to"
                                class="text-sm text-gray-300 hover:text-white transition-colors"
                            >
                                {{ item.label }}
                            </RouterLink>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-sm uppercase tracking-wider text-gray-400 mb-5">
                        {{ t('footer.stayUpdated') }}
                    </h4>

                    <p class="text-sm text-gray-400 mb-4">
                        {{ t('footer.stayUpdatedText') }}
                    </p>

                    <div class="flex gap-2">
                        <input
                            v-model="email"
                            type="email"
                            :placeholder="t('footer.emailPlaceholder')"
                            class="bg-gray-800 border border-gray-700 text-white placeholder:text-gray-500 rounded-xl px-4 py-2 w-full outline-none"
                        />
                        <button
                            class="bg-[#2A7DE1] hover:bg-[#1E6BC9] rounded-xl px-3 shrink-0 inline-flex items-center justify-center"
                            type="button"
                        >
                            <ArrowRight class="w-4 h-4 text-white" />
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-16 pt-8 border-t border-gray-800 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    {{ t('footer.rights') }}
                </p>
                <p class="text-sm text-gray-500 flex items-center gap-1.5">
                    {{ t('footer.madeWithLovePrefix') }}
                    <Heart class="w-4 h-4 text-red-400 fill-red-400" />
                    {{ t('footer.madeWithLoveSuffix') }}
                </p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
    ArrowRight,
    Facebook,
    Heart,
    Instagram,
    Mail,
    MapPin,
    Phone,
    Youtube,
} from 'lucide-vue-next'

const { t } = useI18n()
const email = ref('')

const quickLinks = computed(() => [
    { label: t('nav.about'), to: '/about' },
    { label: t('nav.cases'), to: '/cases' },
    { label: t('nav.transparency'), to: '/transparency' },
    { label: t('footer.volunteers'), to: '/volunteer' },
    { label: t('footer.partners'), to: '/partners' },
])

const supportLinks = computed(() => [
    { label: t('nav.faq'), to: '/faq' },
    { label: t('nav.contact'), to: '/contact' },
])

const socialLinks = [
    {
        key: 'facebook',
        label: 'Facebook',
        icon: Facebook,
        href: '#',
    },
    {
        key: 'instagram',
        label: 'Instagram',
        icon: Instagram,
        href: '#',
    },
    {
        key: 'youtube',
        label: 'YouTube',
        icon: Youtube,
        href: '#',
    },
]
</script>
