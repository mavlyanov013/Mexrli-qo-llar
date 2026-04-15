<template>
    <div class="pt-24 pb-20 min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                    {{ t('transparencyPage.title') }}
                </h1>
                <p class="text-gray-500 mt-2">
                    {{ t('transparencyPage.subtitle') }}
                </p>
            </div>

            <!-- Stats -->
            <TodayStats :donations="donations" :t="t" />

            <!-- 🔥 DIAGRAM (TO‘G‘RI JOY) -->
            <div class="mt-8">
                <MonthlyOverviewChart :donations="donations" :t="t" />
            </div>

            <!-- 🔥 PASTKI QISM -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
                <!-- LEFT: Payment History -->
                <div class="lg:col-span-2">
                    <PaymentHistoryTable
                        :donations="donations"
                        :cases="cases"
                        :t="t"
                    />
                </div>

                <!-- RIGHT: Live Feed -->
                <div>
                    <LiveDonationsFeed
                        :donations="recentDonations"
                        :cases="cases"
                        :t="t"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import TodayStats from '@/components/transparency/TodayStats.vue'
import MonthlyOverviewChart from '@/components/transparency/MonthlyOverviewChart.vue'
import PaymentHistoryTable from '@/components/transparency/PaymentHistoryTable.vue'
import LiveDonationsFeed from '@/components/transparency/LiveDonationsFeed.vue'

import donationService from '@/services/donationService'
import caseService from '@/services/caseService'

const { t } = useI18n()

const donations = ref([])
const cases = ref([])

const recentDonations = computed(() => {
    return [...donations.value]
        .sort((a, b) => {
            const dateA = new Date(a.created_at || a.created_date || 0)
            const dateB = new Date(b.created_at || b.created_date || 0)
            return dateB - dateA
        })
        .slice(0, 20)
})

const fetchData = async () => {
    try {
        const [donationsRes, casesRes] = await Promise.all([
            donationService.getPublicDonations(),
            caseService.getAllCases(),
        ])

        donations.value = Array.isArray(donationsRes) ? donationsRes : []

        cases.value = Array.isArray(casesRes?.data)
            ? casesRes.data
            : Array.isArray(casesRes)
                ? casesRes
                : []
    } catch (error) {
        console.error('Transparency page fetch error:', error)
        donations.value = []
        cases.value = []
    }
}

onMounted(fetchData)
</script>
