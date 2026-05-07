import api from './api'

export default {
    async getLatest(params = {}) {
        const res = await api.get('/news', { params: { ...params, published_only: true } })
        return res.data.data ?? []
    },

    async getBySlug(slug) {
        const res = await api.get(`/news/${slug}`)
        return res.data.data ?? null
    },

    async getAdminList(params = {}) {
        const res = await api.get('/admin/news', { params })
        return res.data
    },

    async create(payload) {
        const res = await api.post('/admin/news', payload)
        return res.data
    },

    async update(id, payload) {
        const res = await api.put(`/admin/news/${id}`, payload)
        return res.data
    },

    async remove(id) {
        const res = await api.delete(`/admin/news/${id}`)
        return res.data
    },
}
