import api from './api'

export default {
    async getBlogPosts(params = {}) {
        const response = await api.get('/blog-posts', { params })
        return response.data.data ?? response.data
    },

    async getBlogPostById(id) {
        const response = await api.get(`/blog-posts/${id}`)
        return response.data.data ?? response.data
    },

    async getAll() {
        const response = await api.get('/admin/blog-posts')
        return response.data.data ?? response.data
    },

    async create(payload) {
        const response = await api.post('/admin/blog-posts', payload)
        return response.data.data ?? response.data
    },

    async update(id, payload) {
        const response = await api.put(`/admin/blog-posts/${id}`, payload)
        return response.data.data ?? response.data
    },

    async delete(id) {
        const response = await api.delete(`/admin/blog-posts/${id}`)
        return response.data.data ?? response.data
    }
}
