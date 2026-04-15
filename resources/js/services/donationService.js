import api from './api'

export default {
    async createDonation(payload) {
        const response = await api.post('/donations', payload)
        return response.data.data ?? response.data
    },

    async initCheckout(payload) {
        const response = await api.post('/billing/checkout/init', payload)
        return response.data.data ?? response.data
    },

    async getCompletedDonations(params = {}) {
        const response = await api.get('/donations/live', { params })
        return response.data.data ?? response.data
    },

    async getPublicDonations(params = {}) {
        const response = await api.get('/donations', { params })
        return response.data.data ?? response.data
    },

    async getAllDonations() {
        const response = await api.get('/admin/donations')
        return response.data.data ?? response.data
    }
}
