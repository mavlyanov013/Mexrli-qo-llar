import api from './api'

export default {
    async getCases(params = {}) {
        const response = await api.get('/admin/cases', { params })
        return response.data.data ?? response.data
    },

    async getCaseById(id) {
        const response = await api.get(`/cases/${id}`)
        return response.data.data ?? response.data
    },

    async getAllCases() {
        const response = await api.get('/admin/cases')
        return response.data.data ?? response.data
    },

    async create(payload) {
        const response = await api.post('/admin/cases', payload)
        return response.data.data ?? response.data
    },

    async update(id, payload) {
        const response = await api.put(`/admin/cases/${id}`, payload)
        return response.data.data ?? response.data
    },

    async delete(id) {
        const response = await api.delete(`/admin/cases/${id}`)
        return response.data.data ?? response.data
    }
}
