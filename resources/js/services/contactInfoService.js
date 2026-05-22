import api from './api'

export default {
    async get() {
        const response = await api.get('/contact-info')
        return response.data?.data ?? response.data
    },

    async getAdmin() {
        const response = await api.get('/admin/contact-info')
        return response.data?.data ?? response.data
    },

    async update(payload) {
        const response = await api.put('/admin/contact-info', payload)
        return response.data
    },
}
