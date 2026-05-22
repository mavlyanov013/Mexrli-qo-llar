<template>
    <div class="pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ t('newsPage.title') }}
                </h1>
            </div>

            <RouterLink
                v-if="featured"
                :to="`/news/${featured.slug}`"
                class="block mb-12 group"
            >
                <div class="relative rounded-2xl overflow-hidden">
                    <img
                        :src="featured.cover_image || '/placeholder.jpg'"
                        :alt="content(featured, 'title')"
                        class="w-full h-[400px] object-cover group-hover:scale-105 transition-transform duration-700"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />
                    <div class="absolute bottom-0 left-0 right-0 p-8">
                        <span class="inline-flex bg-[#FF9800] text-white border-0 mb-3 px-3 py-1 rounded-full text-sm font-medium">
                            {{ t('newsPage.featured') }}
                        </span>
                        <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                            {{ content(featured, 'title') }}
                        </h2>
                        <p class="text-white/80 line-clamp-2">
                            {{ content(featured, 'excerpt') }}
                        </p>
                    </div>
                </div>
            </RouterLink>

            <div class="flex flex-wrap gap-2 mb-8">
                <button
                    v-for="item in categories"
                    :key="item"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm border transition"
                    :class="activeType === item
                        ? 'bg-[#2A7DE1] text-white border-[#2A7DE1]'
                        : 'bg-white text-gray-700 border-gray-300 hover:border-gray-400'"
                    @click="changeType(item)"
                >
                    {{ formatFilterCategory(item) }}
                </button>
            </div>

            <div v-if="loading && posts.length === 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="i in 3"
                    :key="i"
                    class="bg-white rounded-2xl overflow-hidden animate-pulse border border-gray-100"
                >
                    <div class="aspect-video bg-gray-200" />
                    <div class="p-5 space-y-3">
                        <div class="h-5 bg-gray-200 rounded w-2/3" />
                        <div class="h-4 bg-gray-100 rounded w-full" />
                        <div class="h-4 bg-gray-100 rounded w-5/6" />
                    </div>
                </div>
            </div>

            <div v-else-if="posts.length === 0" class="text-center py-20">
                <div class="text-5xl text-gray-300 mb-4">📰</div>
                <p class="text-gray-500">{{ t('newsPage.noPosts') }}</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <RouterLink
                    v-for="post in listPosts"
                    :key="post.id"
                    :to="`/news/${post.slug}`"
                    class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 group"
                >
                    <div class="aspect-video overflow-hidden">
                        <img
                            :src="post.cover_image || '/placeholder.jpg'"
                            :alt="content(post, 'title')"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                    </div>

                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3 flex-wrap">
                            <span class="bg-blue-50 text-[#2A7DE1] border-0 text-xs px-3 py-1 rounded-full font-medium">
                                {{ formatCategory(post.category) }}
                            </span>

                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <span>🕒</span>
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

            <div v-if="hasMore" class="mt-10 text-center">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl font-semibold border-2 border-gray-300 px-6 py-3 hover:border-[#2A7DE1] hover:text-[#2A7DE1] transition disabled:opacity-50"
                    :disabled="loadingMore"
                    @click="loadMore"
                >
                    {{ loadingMore ? t('newsPage.loadingMore') : t('newsPage.loadMore') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { RouterLink } from 'vue-router'
import newsService from '../services/newsService'
import { useLocalizedDisplay } from '@/composables/useLocalizedDisplay'

const { t, locale } = useI18n()
const { content } = useLocalizedDisplay()

const categories = ['all', 'news', 'success_story', 'announcement']

const posts = ref([])
const loading = ref(false)
const loadingMore = ref(false)
const activeType = ref('all')
const currentPage = ref(1)
const lastPage = ref(1)

const listPosts = computed(() => {
    if (!featured.value) return posts.value
    return posts.value.filter((post) => post.id !== featured.value.id)
})

const featured = computed(() => {
    return posts.value.find((post) => post.is_featured) || posts.value[0] || null
})

const hasMore = computed(() => currentPage.value < lastPage.value)

const fetchPosts = async ({ page = 1, append = false } = {}) => {
    if (append) {
        loadingMore.value = true
    } else {
        loading.value = true
    }

    try {
        const params = { page }

        if (activeType.value !== 'all') {
            params.type = activeType.value
        }

        const res = await newsService.getLatest(params)

        if (append) {
            posts.value = [...posts.value, ...(res.data ?? [])]
        } else {
            posts.value = res.data ?? []
        }

        currentPage.value = res.meta?.current_page || page
        lastPage.value = res.meta?.last_page || 1
    } finally {
        loading.value = false
        loadingMore.value = false
    }
}

const changeType = (type) => {
    if (activeType.value === type) return
    activeType.value = type
    currentPage.value = 1
    fetchPosts({ page: 1 })
}

const loadMore = () => {
    if (!hasMore.value || loadingMore.value) return
    fetchPosts({ page: currentPage.value + 1, append: true })
}

const formatFilterCategory = (value) => {
    return t(`newsPage.categories.${value}`)
}

const formatCategory = (value) => {
    if (!value) return t('newsPage.defaultCategory')
    return t(`newsPage.categories.${value}`)
}

const formatDate = (value) => {
    if (!value) return ''

    const localeMap = {
        uz: 'uz-UZ',
        uz_cyrl: 'uz-UZ',
        ru: 'ru-RU',
    }

    return new Date(value).toLocaleDateString(localeMap[locale.value] || 'uz-UZ', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })
}

onMounted(() => fetchPosts())
</script>
