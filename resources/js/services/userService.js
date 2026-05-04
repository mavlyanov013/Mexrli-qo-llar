import api from './api'

export default {
    getAll(params = {}) {
        return api.get('/admin/users', { params }).then((r) => r.data)
    },
    getById(id) {
        return api.get(`/admin/users/${id}`).then((r) => r.data)
    },
    create(payload) {
        return api.post('/admin/users', payload).then((r) => r.data)
    },
    update(id, payload) {
        return api.put(`/admin/users/${id}`, payload).then((r) => r.data)
    },
    remove(id) {
        return api.delete(`/admin/users/${id}`).then((r) => r.data)
    },
}
