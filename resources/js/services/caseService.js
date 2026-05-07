import api from './api'
import { normalizeItem, normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

export default {
    async fetchList(params = {}) {
        const endpoint = params.admin ? '/admin/cases' : '/cases'
        const requestParams = { ...params }
        delete requestParams.admin
        try {
            const response = await api.get(endpoint, { params: requestParams })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Failed to fetch cases') }
        }
    },

    async getCases(params = {}) {
        const response = await api.get('/admin/cases', { params })
        return response.data.data ?? response.data
    },

    async getCaseById(id) {
        try {
            const response = await api.get(`/admin/cases/${id}`)
            return {
                data: response.data.data ?? null,
                error: null
            }
        } catch (error) {
            return { data: null, error }
        }
    },

    async getAllCases() {
        const response = await api.get('/admin/cases')
        return response.data.data ?? response.data
    },

    async create(payload) {
        try {
            const response = await api.post('/admin/cases', payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to create case') }
        }
    },

    async update(id, payload) {
        try {
            const response = await api.put(`/admin/cases/${id}`, payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to update case') }
        }
    },

    async remove(id) {
        try {
            await api.delete(`/admin/cases/${id}`)
            return { error: null }
        } catch (error) {
            return { error: toServiceError(error, 'Failed to delete case') }
        }
    },

    async close(id) {
        return this.update(id, { status: 'closed' })
    }
}
