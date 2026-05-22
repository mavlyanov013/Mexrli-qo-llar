import api from './api'
import { normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

export default {
    async createHelpRequest(payload) {
        const response = await api.post('/help-requests', payload)
        return response.data.data ?? response.data
    },

    async fetchList(params = {}) {
        try {
            const response = await api.get('/admin/help-requests', { params })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Yordam so‘rovlarini yuklab bo‘lmadi') }
        }
    },

    async getAll() {
        const result = await this.fetchList()
        return result.data
    },

    async getById(id) {
        const response = await api.get(`/admin/help-requests/${id}`)
        return response.data
    },

    async updateStatus(id, payload) {
        const response = await api.patch(`/admin/help-requests/${id}/status`, payload)
        return response.data
    },

    async update(id, payload) {
        const response = await api.put(`/admin/help-requests/${id}`, payload)
        return response.data.data ?? response.data
    },

    async approve(id) {
        const response = await api.post(`/admin/help-requests/${id}/approve`)
        return response.data.data ?? response.data
    },

    async reject(id, payload = {}) {
        const response = await api.post(`/admin/help-requests/${id}/reject`, payload)
        return response.data.data ?? response.data
    },

    async convertToCase(id, payload = {}) {
        const response = await api.post(`/admin/help-requests/${id}/convert-to-case`, payload)
        return response.data.data ?? response.data
    }
}
