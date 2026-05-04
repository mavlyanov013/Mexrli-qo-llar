import api from './api'
import { normalizeList, normalizeItem, normalizeMeta, toServiceError } from './serviceHelpers'

const partnerService = {
    async getAll(params = {}) {
        try {
            const response = await api.get('/partners', { params })
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
}

export default partnerService
