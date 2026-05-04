import api from './api'

export default {
    getAll() {
        return api.get('/admin/settings').then((r) => r.data)
    },
    update(items) {
        return api.put('/admin/settings', { items }).then((r) => r.data)
    },
}
