import { ref } from 'vue'
import caseService from '@/services/caseService'

export function useCases() {
    const cases = ref([])
    const meta = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchCases = async (params = {}) => {
        loading.value = true
        error.value = null
        const result = await caseService.fetchList(params)
        cases.value = result.data || []
        meta.value = result.meta
        error.value = result.error
        loading.value = false
    }

    const createCase = async (payload) => caseService.create(payload)
    const updateCase = async (id, payload) => caseService.update(id, payload)
    const closeCase = async (id) => caseService.close(id)
    const deleteCase = async (id) => caseService.remove(id)

    return { cases, meta, loading, error, fetchCases, createCase, updateCase, closeCase, deleteCase }
}
