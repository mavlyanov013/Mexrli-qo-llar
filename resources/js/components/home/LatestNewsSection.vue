<template>
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <SectionHeader :title="t('latestNews.title')" />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <RouterLink
                    v-for="post in visiblePosts"
                    :key="post.id"
                    :to="`/news/${post.slug || post.id}`"
                    class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 group transition hover:shadow-md"
                >
                    <div class="aspect-video overflow-hidden">
                        <img
                            :src="post.cover_image || '/placeholder.jpg'"
                            :alt="post.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                    </div>

                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3 flex-wrap">
                            <span class="bg-blue-50 text-[#2A7DE1] border-0 text-xs px-3 py-1 rounded-full font-medium">
                                {{ formatCategory(post.category) }}
                            </span>

                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <Clock3 class="w-3.5 h-3.5" />
                                {{ formatDate(post.created_at || post.created_date) }}
                            </span>
                        </div>

                        <h3 class="font-bold text-gray-900 group-hover:text-[#2A7DE1] transition-colors line-clamp-2">
                            {{ content(post, 'title') }}
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 line-clamp-2">
                            {{ content(post, 'excerpt') }}
                        </p>
                    </div>
                </RouterLink>
            </div>

            <div class="mt-10 text-center">
                <RouterLink
                    to="/news"
                    class="inline-flex items-center gap-2 rounded-xl font-semibold border-2 border-gray-300 px-5 py-3 hover:border-gray-400 transition"
                >
                    {{ t('latestNews.viewAll') }}
                    <span>→</span>
                </RouterLink>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useLocalizedDisplay } from '@/composables/useLocalizedDisplay'
import SectionHeader from '../shared/SectionHeader.vue'
import { Clock3 } from 'lucide-vue-next'

const { t, locale } = useI18n()
const { content } = useLocalizedDisplay()

const props = defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
})

const visiblePosts = computed(() => props.posts.slice(0, 3))

const formatCategory = (category) => {
    if (!category) return t('latestNews.defaultCategory')
    return category.replace(/_/g, ' ')
}

const formatDate = (value) => {
    if (!value) return ''

    const date = new Date(value)

    const localeMap = {
        uz: 'uz-UZ',
        uz_cyrl: 'uz-UZ',
        ru: 'ru-RU',
    }

    return date.toLocaleDateString(localeMap[locale.value] || 'uz-UZ', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })
}
</script>
