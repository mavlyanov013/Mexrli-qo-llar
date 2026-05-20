<template>
    <div class="pt-24 pb-20 min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="mb-10">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                    {{ t('transparencyPage.title') }}
                </h1>
                <p class="text-gray-500 mt-2">
                    {{ t('transparencyPage.subtitle') }}
                </p>
            </div>

            <ListState :loading="loading" :error="error" :empty="false">
                <TodayStats :donations="donations" :t="t" />

                <div class="mt-8">
                    <MonthlyOverviewChart :donations="donations" :t="t" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
                    <div class="lg:col-span-2">
                        <PaymentHistoryTable :donations="donations" :cases="cases" :t="t" />
                    </div>

                    <div>
                        <LiveDonationsFeed :donations="recentDonations" :cases="cases" :t="t" />
                    </div>
                </div>

<!--                <div class="mt-8">-->
<!--                    <PublishedReportsSection :reports="reports" :t="t" />-->
<!--                </div>-->
            </ListState>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import TodayStats from '@/components/transparency/TodayStats.vue'
import MonthlyOverviewChart from '@/components/transparency/MonthlyOverviewChart.vue'
import PaymentHistoryTable from '@/components/transparency/PaymentHistoryTable.vue'
import LiveDonationsFeed from '@/components/transparency/LiveDonationsFeed.vue'
import PublishedReportsSection from '@/components/transparency/PublishedReportsSection.vue'
import ListState from '@/components/shared/ListState.vue'
import { useTransparencyDashboard } from '@/composables/useTransparencyDashboard'

const { t } = useI18n()
const { donations, cases, reports, recentDonations, loading, error } = useTransparencyDashboard()
</script>
