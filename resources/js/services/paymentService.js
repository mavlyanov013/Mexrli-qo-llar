import api from './api'
import { normalizeItem, normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

export default {
    async fetchList(params = {}) {
        try {
            const response = await api.get('/admin/payments', { params })
            const body = response?.data
            const data = Array.isArray(body) ? body : normalizeList(response)

            return { data, meta: null, error: null }
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
        try {
            const response = await api.get(`/admin/payments/${id}`)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to fetch payment') }
        }
    },

    async create(payload) {
        try {
            const response = await api.post('/admin/payments', payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to create payment') }
        }
    },

    async update(id, payload) {
        try {
            const response = await api.put(`/admin/payments/${id}`, payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to update payment') }
        }
    },

    async remove(id) {
        try {
            await api.delete(`/admin/payments/${id}`)
            return { error: null }
        } catch (error) {
            return { error: toServiceError(error, 'Failed to delete payment') }
        }
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
