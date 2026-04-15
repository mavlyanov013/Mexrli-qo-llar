<template>
    <div>
        <LiveActivityBar />

        <HeroSection
            :hero-image="heroImage"
            :featured-case="featuredCase"
        />

        <StatsSection
            :cases="cases"
            :donations="donations"
            :volunteers="volunteers"
        />

        <ServicesSection />
        <TodayDonationsSection />

        <UrgentCasesSection :cases="cases" />

        <LatestNewsSection
            v-if="posts.length"
            :posts="posts"
        />

        <SuccessStoriesSection :success-image="successImage" />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { getHomeData } from '../services/homeService'

import LiveActivityBar from '../components/home/LiveActivityBar.vue'
import HeroSection from '../components/home/HeroSection.vue'
import StatsSection from '../components/home/StatsSection.vue'
import TodayDonationsSection from '../components/home/TodayDonationsSection.vue'
import UrgentCasesSection from '../components/home/UrgentCasesSection.vue'
import LatestNewsSection from '../components/home/LatestNewsSection.vue'
import SuccessStoriesSection from '../components/home/SuccessStoriesSection.vue'
import ServicesSection from "../components/home/ServicesSection.vue"

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

const load = async () => {
    try {
        const data = await getHomeData()

        cases.value = data.cases || []
        partners.value = data.partners || []
        posts.value = data.posts || []
        donations.value = data.donations || []
        volunteers.value = data.volunteers || []

        featuredCase.value =
            cases.value.find((item) => item.is_featured && item.is_urgent) ||
            cases.value[0] ||
            null
    } catch (error) {
        console.error('Home load error:', error)
    }
}

onMounted(load)
</script>
