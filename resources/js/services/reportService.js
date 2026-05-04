import api from './api'
import { normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

const reportService = {
    async fetchList(params = {}) {
        try {
            const response = await api.get('/financial-reports', { params })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Failed to fetch reports') }
        }
    },
}

export default reportService
