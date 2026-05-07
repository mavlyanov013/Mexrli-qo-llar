import api from './api'

export default {
    async getPublicList(params = {}) {
        const res = await api.get('/faq', { params })
        return res.data.data ?? []
    },

    async getAdminList(params = {}) {
        const res = await api.get('/admin/faq', { params })
        return res.data
    },

    async create(payload) {
        const res = await api.post('/admin/faq', payload)
        return res.data
    },

    async update(id, payload) {
        const res = await api.put(`/admin/faq/${id}`, payload)
        return res.data
    },

    async remove(id) {
        const res = await api.delete(`/admin/faq/${id}`)
        return res.data
    },
}
