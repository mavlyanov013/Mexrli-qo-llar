import { ref } from 'vue'
import donationService from '@/services/donationService'

export function useDonations() {
    const donations = ref([])
    const meta = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchDonations = async (params = {}) => {
        loading.value = true
        error.value = null
        const result = await donationService.fetchList(params)
        donations.value = result.data || []
        meta.value = result.meta
        error.value = result.error
        loading.value = false
    }

    return { donations, meta, loading, error, fetchDonations }
}
