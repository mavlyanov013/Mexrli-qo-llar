<template>
    <div class="pt-24 pb-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <RouterLink
                to="/news"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#2A7DE1] mb-6 transition-colors"
            >
                <span>←</span>
                {{ t('newsDetailPage.backToNewsTop') }}
            </RouterLink>

            <div v-if="loading" class="animate-pulse space-y-6">
                <div class="h-8 bg-gray-200 rounded w-2/3" />
                <div class="h-64 bg-gray-200 rounded-2xl" />
                <div class="h-4 bg-gray-200 rounded w-full" />
                <div class="h-4 bg-gray-200 rounded w-3/4" />
            </div>

            <template v-else-if="post">
                <div class="flex items-center gap-3 mb-4 flex-wrap">
                    <span class="bg-blue-50 text-[#2A7DE1] border-0 px-3 py-1 rounded-full text-sm font-medium">
                        {{ formatCategory(post.category) }}
                    </span>

                    <span class="text-sm text-gray-400 flex items-center gap-1">
                        <span>🕒</span>
                        {{ formatDate(post.created_at || post.created_date) }}
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    {{ post.title }}
                </h1>

                <p v-if="post.author" class="text-sm text-gray-500 mb-6">
                    {{ t('newsDetailPage.byAuthor', { author: post.author }) }}
                </p>

                <img
                    v-if="post.cover_image"
                    :src="post.cover_image"
                    :alt="post.title"
                    class="w-full rounded-2xl mb-8 aspect-video object-cover"
                />

                <div class="prose prose-gray prose-lg max-w-none">
                    <div class="text-gray-700 leading-8 whitespace-pre-line">
                        {{ post.content || post.excerpt || '' }}
                    </div>
                </div>

                <div
                    v-if="linkedCase"
                    class="mt-8 p-5 bg-blue-50 border border-blue-100 rounded-2xl"
                >
                    <p class="text-xs font-semibold text-[#2A7DE1] uppercase tracking-widest mb-3">
                        {{ t('newsDetailPage.linkedCase') }}
                    </p>

                    <div class="flex items-center gap-4">
                        <img
                            v-if="linkedCase.photo_url"
                            :src="linkedCase.photo_url"
                            :alt="linkedCase.name"
                            class="w-16 h-16 rounded-xl object-cover shrink-0"
                        />

                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 truncate">
                                {{ linkedCase.name }}
                            </p>

                            <p
                                v-if="linkedCase.condition"
                                class="text-sm text-gray-500 truncate"
                            >
                                {{ linkedCase.condition }}
                            </p>

                            <p
                                v-if="linkedCase.location"
                                class="text-xs text-gray-400 flex items-center gap-1 mt-0.5"
                            >
                                <span>📍</span>{{ linkedCase.location }}
                            </p>
                        </div>

                        <RouterLink
                            :to="`/cases/${linkedCase.id}`"
                            class="shrink-0 inline-flex items-center gap-2 bg-[#FF9800] hover:bg-[#F57C00] text-white rounded-xl px-4 py-2 text-sm font-medium"
                        >
                            <span>❤</span>
                            {{ t('newsDetailPage.help') }}
                        </RouterLink>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-200 flex items-center justify-between">
                    <RouterLink
                        to="/news"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 hover:bg-gray-50"
                    >
                        <span>←</span>
                        {{ t('newsDetailPage.backToNewsBottom') }}
                    </RouterLink>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl hover:bg-gray-100"
                        @click="sharePost"
                    >
                        ↗
                    </button>
                </div>
            </template>

            <div v-else class="text-center py-16 text-gray-500">
                {{ t('newsDetailPage.notFound') }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { RouterLink, useRoute } from 'vue-router'
import blogService from '../services/blogService'
import caseService from '../services/caseService'

const { t, locale } = useI18n()
const route = useRoute()

const loading = ref(false)
const post = ref(null)
const linkedCase = ref(null)

const fetchPost = async () => {
    loading.value = true

    try {
        post.value = await blogService.getBlogPostById(route.params.id)

        if (post.value?.case_id) {
            const caseResult = await caseService.getCaseById(post.value.case_id)
            linkedCase.value = caseResult.data || null
        }
    } catch (error) {
        console.error('News detail load error:', error)
        post.value = null
    } finally {
        loading.value = false
    }
}

const formatCategory = (value) => {
    if (!value) return t('newsDetailPage.defaultCategory')
    return t(`newsPage.categories.${value}`)
}

const formatDate = (value) => {
    if (!value) return ''

    const localeMap = {
        en: 'en-US',
        uz: 'uz-UZ',
        ru: 'ru-RU',
    }

    return new Date(value).toLocaleDateString(localeMap[locale.value] || 'en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    })
}

const sharePost = async () => {
    try {
        if (navigator.share && post.value) {
            await navigator.share({
                url: window.location.href,
                title: post.value.title,
            })
        }
    } catch (error) {
        console.error('Share failed:', error)
    }
}

onMounted(fetchPost)
</script>
