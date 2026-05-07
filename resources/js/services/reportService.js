import api from './api'
import { normalizeItem, normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

const reportService = {
    async fetchList(params = {}) {
        const endpoint = params.admin ? '/admin/reports' : '/financial-reports'
        const requestParams = { ...params }
        delete requestParams.admin
        try {
            const response = await api.get(endpoint, { params: requestParams })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Failed to fetch reports') }
        }
    },

    async create(payload) {
        try {
            const response = await api.post('/admin/reports', payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to create report') }
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/admin/reports/${id}`)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to fetch report') }
        }
    },

    async update(id, payload) {
        try {
            const response = await api.put(`/admin/reports/${id}`, payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to update report') }
        }
    },

    async remove(id) {
        try {
            await api.delete(`/admin/reports/${id}`)
            return { error: null }
        } catch (error) {
            return { error: toServiceError(error, 'Failed to delete report') }
        }
    },
}

export default reportService
