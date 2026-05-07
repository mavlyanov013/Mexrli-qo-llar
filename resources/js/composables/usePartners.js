import { onMounted, ref } from 'vue'
import partnerService from '@/services/partnerService'

export function usePartners() {
    const partners = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchPartners = async (params = {}) => {
        loading.value = true
        error.value = null
        const result = await partnerService.getAll(params)
        const shouldFilterOnlyActive = !params.admin
        partners.value = shouldFilterOnlyActive
            ? (result.data || []).filter((item) => item.is_active !== false)
            : (result.data || [])
        error.value = result.error
        loading.value = false
    }

    const createPartner = async (payload) => partnerService.create(payload)
    const updatePartner = async (id, payload) => partnerService.update(id, payload)
    const deletePartner = async (id) => partnerService.remove(id)
    const togglePartnerStatus = async (id, isActive) => partnerService.toggleStatus(id, isActive)

    onMounted(() => {
        fetchPartners()
    })

    return {
        partners,
        loading,
        error,
        fetchPartners,
        createPartner,
        updatePartner,
        deletePartner,
        togglePartnerStatus,
    }
}
