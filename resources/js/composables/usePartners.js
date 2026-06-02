import { onMounted, ref } from 'vue'
import partnerService from '@/services/partnerService'

export function usePartners({ autoFetch = true } = {}) {
    const partners = ref([])
    const meta = ref(null)
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
        meta.value = result.meta
        error.value = result.error
        loading.value = false
    }

    const createPartner = async (payload) => partnerService.create(payload)
    const updatePartner = async (id, payload) => partnerService.update(id, payload)
    const deletePartner = async (id) => partnerService.remove(id)
    const togglePartnerStatus = async (id, isActive) => partnerService.toggleStatus(id, isActive)

    onMounted(() => {
        if (autoFetch) {
            fetchPartners()
        }
    })

    return {
        partners,
        meta,
        loading,
        error,
        fetchPartners,
        createPartner,
        updatePartner,
        deletePartner,
        togglePartnerStatus,
    }
}
