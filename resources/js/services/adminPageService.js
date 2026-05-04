import api from './api'

export default {
    getAll(params = {}) {
        return api.get('/admin/pages', { params }).then((r) => r.data)
    },
    getById(id) {
        return api.get(`/admin/pages/${id}`).then((r) => r.data)
    },
    create(payload) {
        return api.post('/admin/pages', payload).then((r) => r.data)
    },
    update(id, payload) {
        return api.put(`/admin/pages/${id}`, payload).then((r) => r.data)
    },
    remove(id) {
        return api.delete(`/admin/pages/${id}`).then((r) => r.data)
    },
    createSection(payload) {
        return api.post('/admin/sections', payload).then((r) => r.data)
    },
    updateSection(id, payload) {
        return api.put(`/admin/sections/${id}`, payload).then((r) => r.data)
    },
    removeSection(id) {
        return api.delete(`/admin/sections/${id}`).then((r) => r.data)
    },
    reorderSections(items) {
        return api.post('/admin/sections/reorder', { items }).then((r) => r.data)
    },
}
