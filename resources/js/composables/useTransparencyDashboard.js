import { computed, onMounted, ref } from 'vue'
import { useDonations } from '@/composables/useDonations'
import caseService from '@/services/caseService'
import reportService from '@/services/reportService'

export function useTransparencyDashboard() {
    const { donations, loading: donationsLoading, error: donationsError, fetchDonations } = useDonations()
    const cases = ref([])
    const reports = ref([])
    const loading = ref(false)
    const error = ref(null)

    const recentDonations = computed(() => {
        return [...(donations.value || [])]
            .sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
            .slice(0, 20)
    })

    const fetchDashboard = async () => {
        loading.value = true
        error.value = null

        await fetchDonations()
        const [casesResult, reportsResult] = await Promise.all([
            caseService.fetchList(),
            reportService.fetchList(),
        ])

        cases.value = casesResult.data || []
        reports.value = reportsResult.data || []
        error.value = donationsError.value || casesResult.error || reportsResult.error
        loading.value = false
    }

    onMounted(fetchDashboard)

    return {
        donations,
        cases,
        reports,
        recentDonations,
        loading: computed(() => loading.value || donationsLoading.value),
        error,
        fetchDashboard,
    }
}
