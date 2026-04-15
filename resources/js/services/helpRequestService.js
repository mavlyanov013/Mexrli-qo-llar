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
    }
}
