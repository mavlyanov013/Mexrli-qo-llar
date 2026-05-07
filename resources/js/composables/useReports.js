import { ref } from 'vue'
import reportService from '@/services/reportService'

export function useReports() {
    const reports = ref([])
    const meta = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchReports = async (params = {}) => {
        loading.value = true
        error.value = null
        const result = await reportService.fetchList(params)
        reports.value = result.data || []
        meta.value = result.meta
        error.value = result.error
        loading.value = false
    }

    const createReport = async (payload) => reportService.create(payload)
    const updateReport = async (id, payload) => reportService.update(id, payload)
    const deleteReport = async (id) => reportService.remove(id)

    return { reports, meta, loading, error, fetchReports, createReport, updateReport, deleteReport }
}
