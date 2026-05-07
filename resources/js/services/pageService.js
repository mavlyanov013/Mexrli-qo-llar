import api from './api'

export default {
    async getAll(params = {}) {
        const res = await api.get('/admin/pages', { params })
        return res.data
    },

    async getById(id) {
        const res = await api.get(`/admin/pages/${id}`)
        return res.data
    },

    async update(id, payload) {
        const res = await api.put(`/admin/pages/${id}`, payload)
        return res.data
    },

    async remove(id) {
        const res = await api.delete(`/admin/pages/${id}`)
        return res.data
    },
    getBySlug(slug) {
        return api.get(`/about`).then((r) => r.data)
    },
}
