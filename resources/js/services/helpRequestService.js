import api from './api'

export default {
    async createHelpRequest(payload) {
        const response = await api.post('/help-requests', payload)
        return response.data.data ?? response.data
    },

    async getAll() {
        const response = await api.get('/admin/help-requests')
        return response.data.data ?? response.data
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
