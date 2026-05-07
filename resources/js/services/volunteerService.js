import api from './api'
import { normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

export default {
    async fetchList(params = {}) {
        try {
            const response = await api.get('/admin/volunteer-applications', { params })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Failed to fetch volunteer applications') }
        }
    },

    async submit(payload) {
        try {
            const response = await api.post('/volunteer-applications', payload)
            return { data: response.data?.data ?? response.data, error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to submit application') }
        }
    },

    async update(id, payload) {
        try {
            const response = await api.put(`/admin/volunteer-applications/${id}`, payload)
            return { data: response.data?.data ?? response.data, error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to update volunteer application') }
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/admin/volunteer-applications/${id}`)
            return { data: response.data?.data ?? response.data, error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to fetch volunteer application') }
        }
    },

    async remove(id) {
        try {
            await api.delete(`/admin/volunteer-applications/${id}`)
            return { error: null }
        } catch (error) {
            return { error: toServiceError(error, 'Failed to delete volunteer application') }
        }
    },

    async createVolunteerApplication(payload) {
        const response = await api.post('/volunteer-applications', payload)
        return response.data.data ?? response.data
    },

    async getAll() {
        const response = await api.get('/admin/volunteer-applications')
        return response.data.data ?? response.data
    }
}
