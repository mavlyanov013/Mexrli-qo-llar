import { ref } from 'vue'
import paymentService from '@/services/paymentService'

export function usePayments() {
    const payments = ref([])
    const meta = ref(null)
    const providers = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchPayments = async (params = {}) => {
        loading.value = true
        error.value = null

        const result = await paymentService.fetchList(params)

        payments.value = result.data || []
        meta.value = result.meta
        error.value = result.error
        loading.value = false
    }

    const fetchProviders = async () => {
        const result = await paymentService.fetchProviderConfigs()
        providers.value = result.data || []
        if (!error.value) error.value = result.error
    }

    return {
        payments,
        meta,
        providers,
        loading,
        error,
        fetchPayments,
        fetchProviders,
    }
}
