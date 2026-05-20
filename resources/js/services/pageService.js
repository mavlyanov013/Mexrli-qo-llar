import api from './api'

const pageService = {
    getBySlug(slug) {
        return api.get(`/pages/${slug}`)
    },

    getById(id) {
        return api.get(`/admin/pages/${id}`)
    },

    getAll() {
        return api.get(`/admin/pages`)
    },
}

export default pageService
