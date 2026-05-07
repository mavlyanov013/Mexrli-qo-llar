import api from './api'
import { normalizeItem, normalizeList, normalizeMeta, toServiceError } from './serviceHelpers'

export default {
    async sendContact(payload) {
        try {
            const response = await api.post('/contact-messages', payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to send message') }
        }
    },

    async fetchList(params = {}) {
        try {
            const response = await api.get('/admin/contact-messages', { params })
            return { data: normalizeList(response), meta: normalizeMeta(response), error: null }
        } catch (error) {
            return { data: [], meta: null, error: toServiceError(error, 'Failed to fetch messages') }
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/admin/contact-messages/${id}`)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to fetch message') }
        }
    },

    async update(id, payload) {
        try {
            const response = await api.put(`/admin/contact-messages/${id}`, payload)
            return { data: normalizeItem(response), error: null }
        } catch (error) {
            return { data: null, error: toServiceError(error, 'Failed to update message') }
        }
    },

    async remove(id) {
        try {
            await api.delete(`/admin/contact-messages/${id}`)
            return { error: null }
        } catch (error) {
            return { error: toServiceError(error, 'Failed to delete message') }
        }
    },

    async getAll() {
        const result = await this.fetchList()
        return result.data
    },
}
