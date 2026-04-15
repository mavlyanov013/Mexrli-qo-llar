<template>
    <div class="pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Trusted Partners
                </h1>
                <p class="text-lg text-gray-500">
                    Organizations that stand with us
                </p>
            </div>

            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="i in 6"
                    :key="i"
                    class="bg-white rounded-2xl p-6 border border-gray-100 animate-pulse"
                >
                    <div class="h-16 w-16 bg-gray-200 rounded-xl mb-4" />
                    <div class="h-5 bg-gray-200 rounded w-2/3 mb-2" />
                    <div class="h-4 bg-gray-100 rounded w-1/3 mb-4" />
                    <div class="h-4 bg-gray-100 rounded w-full" />
                </div>
            </div>

            <div v-else-if="partners.length === 0" class="text-center py-20">
                <div class="text-5xl text-gray-300 mb-4">🏢</div>
                <p class="text-gray-500">Partner organizations will appear here.</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="partner in partners"
                    :key="partner.id"
                    class="bg-white rounded-2xl p-6 border border-gray-100"
                >
                    <div class="flex items-start gap-4">
                        <img
                            v-if="partner.logo_url"
                            :src="partner.logo_url"
                            :alt="partner.name"
                            class="w-16 h-16 object-contain rounded-xl"
                        />
                        <div
                            v-else
                            class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center text-xl font-bold text-gray-400"
                        >
                            {{ partner.name?.[0] }}
                        </div>

                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">{{ partner.name }}</h3>
                            <span
                                class="inline-flex border-0 mt-1 px-3 py-1 rounded-full text-sm font-medium"
                                :class="typeClass(partner.type)"
                            >
                                {{ formatType(partner.type) }}
                            </span>
                        </div>
                    </div>

                    <p v-if="partner.description" class="text-sm text-gray-500 mt-4">
                        {{ partner.description }}
                    </p>

                    <a
                        v-if="partner.website"
                        :href="partner.website"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-sm text-[#2A7DE1] hover:underline mt-3"
                    >
                        Visit website ↗
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const partners = ref([])
const loading = ref(false)

const fetchPartners = async () => {
    loading.value = true
    try {
        const response = await api.get('/partners')
        partners.value = response?.data?.data || []
    } catch (error) {
        console.error('Partners load error:', error)
        partners.value = []
    } finally {
        loading.value = false
    }
}

const formatType = (value) => (value || 'corporate').replace(/_/g, ' ')

const typeClass = (type) => {
    if (type === 'ngo') return 'bg-green-50 text-[#4CAF50]'
    if (type === 'government') return 'bg-purple-50 text-purple-600'
    if (type === 'medical') return 'bg-red-50 text-red-600'
    if (type === 'media') return 'bg-orange-50 text-[#FF9800]'
    return 'bg-blue-50 text-[#2A7DE1]'
}

onMounted(fetchPartners)
</script>
