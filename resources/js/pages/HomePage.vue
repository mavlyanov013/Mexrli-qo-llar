<template>
    <div>
        <HeroSection
            :hero-image="heroImage"
            :featured-case="featuredCase"
        />

        <StatsSection
            :cases="cases"
            :donations="donations"
            :volunteers="volunteers"
            :posts="posts"
        />

        <ServicesSection />
        <TodayDonationsSection />

        <UrgentCasesSection :cases="cases" />

        <LatestNewsSection
            v-if="posts.length"
            :posts="posts"
        />

        <SuccessStoriesSection
            :success-image="successImage"
            :posts="posts"
        />
        <FinalCtaSection />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { getHomeData } from '../services/homeService'

import HeroSection from '../components/home/HeroSection.vue'
import StatsSection from '../components/home/StatsSection.vue'
import TodayDonationsSection from '../components/home/TodayDonationsSection.vue'
import UrgentCasesSection from '../components/home/UrgentCasesSection.vue'
import LatestNewsSection from '../components/home/LatestNewsSection.vue'
import SuccessStoriesSection from '../components/home/SuccessStoriesSection.vue'
import ServicesSection from "../components/home/ServicesSection.vue"
import FinalCtaSection from '../components/home/FinalCtaSection.vue'

const cases = ref([])
const partners = ref([])
const posts = ref([])
const donations = ref([])
const volunteers = ref([])
const featuredCase = ref(null)

const heroImage =
    'https://qtrypzzcjebvfcihiynt.supabase.co/storage/v1/object/public/base44-prod/public/69b16688c1d72c0a0a9771b2/561d3d966_generated_674ff45e.png'

const successImage =
    'https://qtrypzzcjebvfcihiynt.supabase.co/storage/v1/object/public/base44-prod/public/69b16688c1d72c0a0a9771b2/f3e35eaac_generated_d3b5f459.png'

const isActiveCase = (item) => {
    const status = String(item?.status || '').toLowerCase()
    return ['active', 'open', 'in_progress'].includes(status)
}

const isUrgentCase = (item) => {
    return Boolean(item?.is_urgent) && isActiveCase(item)
}

const isFeaturedUrgentCase = (item) => {
    return Boolean(item?.is_featured) && Boolean(item?.is_urgent) && isActiveCase(item)
}

const selectFeaturedCase = (items) => {
    if (!Array.isArray(items) || !items.length) {
        return null
    }

    return (
        items.find(isFeaturedUrgentCase) ||
        items.find(isUrgentCase) ||
        items.find((item) => Boolean(item?.is_featured) && isActiveCase(item)) ||
        items.find(isActiveCase) ||
        items[0] ||
        null
    )
}

const load = async () => {
    try {
        const data = await getHomeData()

        cases.value = Array.isArray(data.cases) ? data.cases : []
        partners.value = Array.isArray(data.partners) ? data.partners : []
        posts.value = Array.isArray(data.posts) ? data.posts : []
        donations.value = Array.isArray(data.donations) ? data.donations : []
        volunteers.value = Array.isArray(data.volunteers) ? data.volunteers : []

        featuredCase.value = selectFeaturedCase(cases.value)
    } catch (error) {
        console.error('Home load error:', error)
        featuredCase.value = null
    }
}

onMounted(load)
</script>
