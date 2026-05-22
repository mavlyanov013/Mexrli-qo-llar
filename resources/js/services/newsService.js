import api from './api'

const normalizeList = (response) => {
    const body = response.data ?? {}

    return {
        data: body.data ?? [],
        meta: body.meta ?? null,
    }
}

export default {
    async getLatest(params = {}) {
        const res = await api.get('/news', {
            params: {
                published_only: true,
                per_page: 12,
                ...params,
            },
        })

        return normalizeList(res)
    },

    async getBySlug(slug) {
        const res = await api.get(`/news/${slug}`)
        return res.data.data ?? null
    },

    async getAdminList(params = {}) {
        const res = await api.get('/admin/news', {
            params: {
                per_page: 12,
                ...params,
            },
        })

        return normalizeList(res)
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

    getById(id) {
        return api.get(`/admin/news/${id}`).then((r) => r.data)
    },
}
