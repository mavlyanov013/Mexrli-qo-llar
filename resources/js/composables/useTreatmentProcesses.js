import { ref } from 'vue'
import treatmentProcessService from '@/services/treatmentProcessService'

export function useTreatmentProcesses() {
    const items = ref([])
    const meta = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchItems = async (params = {}) => {
        loading.value = true
        error.value = null

        try {
            const result = await treatmentProcessService.getList(params)
            items.value = result.data ?? []
            meta.value = result.meta ?? null
        } catch (err) {
            items.value = []
            meta.value = null
            error.value = err?.response?.data?.message || err?.message || 'Failed to fetch treatment processes'
        } finally {
            loading.value = false
        }
    }

    const removeItem = async (id, params = {}) => {
        await treatmentProcessService.remove(id)
        await fetchItems({ page: meta.value?.current_page || 1, ...params })
    }

    return {
        items,
        meta,
        loading,
        error,
        fetchItems,
        removeItem,
    }
}
