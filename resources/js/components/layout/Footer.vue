<template>
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 lg:gap-12">
                <div>
                    <div class="flex items-center gap-3 mb-5">
                        <img
                            :src="siteLogo"
                            :alt="t('common.brandName')"
                            class="w-11 h-11 object-contain rounded-xl"
                        />
                        <span class="text-xl font-bold tracking-tight">{{ t('common.brandName') }}</span>
                    </div>

                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        {{ t('footer.description') }}
                    </p>

                    <div v-if="socialLinks.length" class="flex gap-3">
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
                        <li v-if="displayAddress" class="flex items-start gap-2.5 text-sm text-gray-300">
                            <MapPin class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" />
                            <span>{{ displayAddress }}</span>
                        </li>

                        <li v-if="displayPhone" class="flex items-center gap-2.5 text-sm text-gray-300">
                            <Phone class="w-4 h-4 text-gray-400 shrink-0" />
                            <a :href="`tel:${displayPhone}`" class="hover:text-white transition-colors">
                                {{ displayPhone }}
                            </a>
                        </li>

                        <li v-if="displayPhone2" class="flex items-center gap-2.5 text-sm text-gray-300">
                            <Phone class="w-4 h-4 text-gray-400 shrink-0" />
                            <a :href="`tel:${displayPhone2}`" class="hover:text-white transition-colors">
                                {{ displayPhone2 }}
                            </a>
                        </li>

                        <li v-if="displayEmail" class="flex items-center gap-2.5 text-sm text-gray-300">
                            <Mail class="w-4 h-4 text-gray-400 shrink-0" />
                            <a :href="`mailto:${displayEmail}`" class="hover:text-white transition-colors">
                                {{ displayEmail }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-16 pt-8 border-t border-gray-800 flex flex-col lg:flex-row items-center justify-between gap-4 text-sm text-gray-500">
                <p class="text-center lg:text-left">
                    {{ t('footer.rights') }}
                </p>

                <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                    <RouterLink to="/faq" class="hover:text-white transition-colors">
                        {{ t('nav.faq') }}
                    </RouterLink>
                    <RouterLink to="/contact" class="hover:text-white transition-colors">
                        {{ t('nav.contact') }}
                    </RouterLink>
                    <RouterLink to="/donate" class="hover:text-white transition-colors">
                        {{ t('nav.donate') }}
                    </RouterLink>
                </div>

                <p class="text-center lg:text-right flex items-center justify-center lg:justify-end gap-1.5">
                    {{ t('footer.madeWithLovePrefix') }}
                    <IconBadge :icon="Heart" tone="red" size="xs" class="inline-flex shrink-0" />
                    {{ t('footer.madeWithLoveSuffix') }}
                </p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { siteLogo } from '@/constants/branding'
import contactInfoService from '@/services/contactInfoService'
import IconBadge from '../shared/IconBadge.vue'
import {
    Facebook,
    Heart,
    Instagram,
    Mail,
    MapPin,
    Phone,
    Send,
    Youtube,
} from 'lucide-vue-next'

const { t } = useI18n()
const contactInfo = ref(null)

const quickLinks = computed(() => [
    { label: t('nav.about'), to: '/about' },
    { label: t('nav.cases'), to: '/cases' },
    { label: t('nav.transparency'), to: '/transparency' },
    { label: t('nav.news'), to: '/news' },
    { label: t('footer.volunteers'), to: '/volunteer' },
    { label: t('footer.partners'), to: '/partners' },
    { label: t('nav.faq'), to: '/faq' },
    { label: t('nav.contact'), to: '/contact' },
])

const displayAddress = computed(() => contactInfo.value?.address || t('footer.location'))
const displayPhone = computed(() => contactInfo.value?.phone || '')
const displayPhone2 = computed(() => contactInfo.value?.phone_2 || '')
const displayEmail = computed(() => contactInfo.value?.email || '')

const socialLinks = computed(() => {
    const data = contactInfo.value || {}
    const links = []

    if (data.facebook_url) {
        links.push({ key: 'facebook', label: 'Facebook', icon: Facebook, href: data.facebook_url })
    }

    if (data.instagram_url) {
        links.push({ key: 'instagram', label: 'Instagram', icon: Instagram, href: data.instagram_url })
    }

    if (data.youtube_url) {
        links.push({ key: 'youtube', label: 'YouTube', icon: Youtube, href: data.youtube_url })
    }

    if (data.telegram_url) {
        links.push({ key: 'telegram', label: 'Telegram', icon: Send, href: data.telegram_url })
    }

    return links
})

onMounted(async () => {
    try {
        contactInfo.value = await contactInfoService.get()
    } catch (error) {
        console.error('Footer contact info load error:', error)
    }
})
</script>
