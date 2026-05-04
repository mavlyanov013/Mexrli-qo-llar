import api from './api'
import { normalizeItem, normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

export default {
    async fetchList(params = {}) {
        try {
            const response = await api.get('/admin/payments', { params })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Failed to fetch payments') }
        }
    },

    async fetchProviderConfigs() {
        try {
            const response = await api.get('/billing/providers')
            return { data: normalizeList(response), error: null }
        } catch (error) {
            return { data: [], error: toServiceError(error, 'Failed to fetch payment providers') }
        }
    },

    async fetchPublicStatus(provider, paymentId) {
        try {
            const response = provider === 'uzumbank'
                ? await api.get(`/billing/uzumbank/status/${paymentId}`)
                : await api.get(`/billing/paynet/status/${paymentId}`)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to fetch payment status') }
        }
    },

    async getAll(params = {}) {
        const response = await api.get('/admin/payments', { params })
        return response.data
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
