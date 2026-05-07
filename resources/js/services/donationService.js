import api from './api'
import { normalizeItem, normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

export default {
    async fetchList(params = {}) {
        const endpoint = params.admin ? '/admin/donations' : '/donations'
        const requestParams = { ...params }
        delete requestParams.admin
        try {
            const response = await api.get(endpoint, { params: requestParams })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Failed to fetch donations') }
        }
    },

    async createDonation(payload) {
        try {
            const response = await api.post('/donations', payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to create donation') }
        }
    },

    async create(payload) {
        try {
            const response = await api.post('/admin/donations', payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to create donation') }
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/admin/donations/${id}`)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to fetch donation') }
        }
    },

    async update(id, payload) {
        try {
            const response = await api.put(`/admin/donations/${id}`, payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to update donation') }
        }
    },

    async remove(id) {
        try {
            await api.delete(`/admin/donations/${id}`)
            return { error: null }
        } catch (error) {
            return { error: toServiceError(error, 'Failed to delete donation') }
        }
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
