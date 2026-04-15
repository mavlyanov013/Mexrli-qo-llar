<template>
    <nav
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        :class="scrolled ? 'backdrop-blur bg-white/80 shadow-sm border-b border-white/20' : 'bg-transparent'"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16 md:h-20">
                <RouterLink to="/" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#2A7DE1] flex items-center justify-center">
                        <span class="text-white">❤</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900 tracking-tight">Mehrli</span>
                </RouterLink>

                <div class="hidden lg:flex items-center gap-1">
                    <RouterLink
                        v-for="item in links"
                        :key="item.to"
                        :to="item.to"
                        class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-[#2A7DE1] rounded-lg hover:bg-blue-50/50 transition-colors"
                    >
                        {{ item.label }}
                    </RouterLink>
                </div>

                <div class="flex items-center gap-2">
                    <div class="hidden md:flex items-center gap-1 border border-gray-300 rounded-xl p-1 bg-white/80">
                        <button
                            v-for="lang in languages"
                            :key="lang.value"
                            type="button"
                            class="px-3 py-1.5 text-sm font-semibold rounded-lg transition-colors"
                            :class="locale === lang.value
                                ? 'bg-[#2A7DE1] text-white'
                                : 'text-gray-600 hover:bg-gray-100'"
                            @click="changeLang(lang.value)"
                        >
                            {{ lang.label }}
                        </button>
                    </div>

                    <button
                        class="hidden sm:block rounded-xl px-4 py-2 border-2 font-semibold border-gray-300"
                        @click="goHelp"
                    >
                        {{ t('nav.requestHelp') }}
                    </button>

                    <RouterLink to="/donate" class="hidden sm:block">
                        <button class="bg-[#FF9800] hover:bg-[#F57C00] text-white rounded-xl gap-1.5 px-5 py-2 font-semibold shadow-md shadow-orange-200/50 inline-flex items-center">
                            ❤ {{ t('nav.donate') }}
                        </button>
                    </RouterLink>

                    <button
                        class="lg:hidden w-10 h-10 inline-flex items-center justify-center"
                        @click="open = !open"
                    >
                        {{ open ? '✕' : '☰' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="open" class="lg:hidden backdrop-blur bg-white/90 border-t border-white/20 shadow-lg">
            <div class="px-4 py-4 space-y-1">
                <div class="flex items-center gap-2 mb-3">
                    <button
                        v-for="lang in languages"
                        :key="lang.value"
                        type="button"
                        class="px-3 py-2 text-sm font-semibold rounded-lg border transition-colors"
                        :class="locale === lang.value
                            ? 'bg-[#2A7DE1] text-white border-[#2A7DE1]'
                            : 'bg-white text-gray-600 border-gray-300'"
                        @click="changeLang(lang.value)"
                    >
                        {{ lang.label }}
                    </button>
                </div>

                <RouterLink
                    v-for="item in links"
                    :key="item.to"
                    :to="item.to"
                    class="block px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 rounded-xl transition-colors"
                    @click="open = false"
                >
                    {{ item.label }}
                </RouterLink>

                <button
                    class="w-full mt-3 rounded-xl border-2 font-semibold border-gray-300 px-4 py-3"
                    @click="mobileHelp"
                >
                    {{ t('nav.requestHelp') }}
                </button>

                <RouterLink to="/donate" @click="open = false">
                    <button class="w-full mt-2 bg-[#FF9800] hover:bg-[#F57C00] text-white rounded-xl gap-1.5 font-semibold px-4 py-3 inline-flex items-center justify-center">
                        ❤ {{ t('nav.donate') }}
                    </button>
                </RouterLink>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()
const router = useRouter()

const open = ref(false)
const scrolled = ref(false)

const languages = [
    { label: 'EN', value: 'en' },
    { label: 'UZ', value: 'uz' },
    { label: 'RU', value: 'ru' },
]

const links = computed(() => [
    { label: t('nav.home'), to: '/' },
    { label: t('nav.about'), to: '/about' },
    { label: t('nav.cases'), to: '/cases' },
    { label: t('nav.transparency'), to: '/transparency' },
    { label: t('nav.volunteer'), to: '/volunteer'},
    { label: t('nav.news'), to: '/news' },
    { label: t('nav.faq'), to: '/faq' },
    { label: t('nav.contact'), to: '/contact' },
])

const changeLang = (lang) => {
    locale.value = lang
    localStorage.setItem('lang', lang)
}

const onScroll = () => {
    scrolled.value = window.scrollY > 20
}

const goHelp = () => {
    router.push('/help-request')
}

const mobileHelp = () => {
    open.value = false
    router.push('/help-request')
}

onMounted(() => {
    window.addEventListener('scroll', onScroll)
})

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll)
})
</script>
