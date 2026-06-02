import api from './api'
import { normalizeItem, normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

export default {
    async fetchList(params = {}) {
        try {
            const response = await api.get('/admin/help-requests', { params })
            return {
                data: normalizeList(response),
                meta: normalizeMeta(response),
                error: null,
            }
        } catch (error) {
            return {
                data: [],
                meta: null,
                error: toServiceError(error, 'Failed to fetch help requests'),
            }
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/admin/help-requests/${id}`)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to fetch help request') }
        }
    },

    async updateStatus(id, payload) {
        const response = await api.patch(`/admin/help-requests/${id}/status`, payload)
        return response.data?.data ?? response.data
    },

    async createHelpRequest(payload) {
        const response = await api.post('/help-requests', payload)
        return response.data?.data ?? response.data
    },
}
