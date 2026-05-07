import api from './api'
import { normalizeList, normalizeItem, normalizeMeta, toServiceError } from './serviceHelpers'

const partnerService = {
    async getAll(params = {}) {
        const endpoint = params.admin ? '/admin/partners' : '/partners'
        const requestParams = { ...params }
        delete requestParams.admin
        try {
            const response = await api.get(endpoint, { params: requestParams })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Failed to fetch partners') }
        }
    },

    async create(payload) {
        try {
            const response = await api.post('/admin/partners', payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to create partner') }
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/admin/partners/${id}`)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to fetch partner') }
        }
    },

    async update(id, payload) {
        try {
            const response = await api.put(`/admin/partners/${id}`, payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to update partner') }
        }
    },

    async remove(id) {
        try {
            await api.delete(`/admin/partners/${id}`)
            return { error: null }
        } catch (error) {
            return { error: toServiceError(error, 'Failed to delete partner') }
        }
    },

    async toggleStatus(id, isActive) {
        return this.update(id, { is_active: isActive })
    },
}

export default partnerService
