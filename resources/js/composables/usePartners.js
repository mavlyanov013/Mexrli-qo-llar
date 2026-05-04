import { onMounted, ref } from 'vue'
import partnerService from '@/services/partnerService'

export function usePartners() {
    const partners = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchPartners = async (params = { active: true }) => {
        loading.value = true
        error.value = null
        const result = await partnerService.getAll(params)
        partners.value = (result.data || []).filter((item) => item.is_active !== false)
        error.value = result.error
        loading.value = false
    }

    onMounted(() => {
        fetchPartners()
    })

    return { partners, loading, error, fetchPartners }
}
