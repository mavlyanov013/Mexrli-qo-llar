import api from './api'

const pageService = {
    getBySlug(slug) {
        return api.get(`/pages/${slug}`).then(r => r.data)
    },

    getAbout() {
        return api.get('/pages/about').then(r => r.data)
    }
}

export default pageService
