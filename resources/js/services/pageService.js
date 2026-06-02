import api from './api'
import { normalizeList, normalizeMeta } from './serviceHelpers'

const pageService = {
    getBySlug(slug) {
        return api.get(`/pages/${slug}`)
    },

    getById(id) {
        return api.get(`/admin/pages/${id}`)
    },

    async getAll(params = {}) {
        const response = await api.get('/admin/pages', { params })
        return {
            data: normalizeList(response),
            meta: normalizeMeta(response),
        }
    },
}

export default pageService
