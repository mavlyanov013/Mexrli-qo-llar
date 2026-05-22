import api from './api'

export default {
    async getList(params = {}) {
        const res = await api.get('/admin/treatment-processes', { params })
        return res.data
    },

    async getById(id) {
        const res = await api.get(`/admin/treatment-processes/${id}`)
        return res.data
    },

    async create(payload) {
        const res = await api.post('/admin/treatment-processes', payload)
        return res.data
    },

    async update(id, payload) {
        const res = await api.put(`/admin/treatment-processes/${id}`, payload)
        return res.data
    },

    async remove(id) {
        const res = await api.delete(`/admin/treatment-processes/${id}`)
        return res.data
    },

    async getPublicByCase(caseId) {
        const res = await api.get(`/cases/${caseId}/treatment-processes`)
        return res.data.data ?? res.data ?? []
    },

}
