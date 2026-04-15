import api from './api'

export default {
    async getAll(params = {}) {
        const response = await api.get('/admin/payments', { params })
        return response.data.data ?? response.data
    },

    async getById(id) {
        const response = await api.get(`/admin/payments/${id}`)
        return response.data.data ?? response.data
    },

    async getPaynetStatus(id) {
        const response = await api.get(`/billing/paynet/status/${id}`)
        return response.data.data ?? response.data
    },

    async getUzumBankStatus(id) {
        const response = await api.get(`/billing/uzumbank/status/${id}`)
        return response.data.data ?? response.data
    },
}
