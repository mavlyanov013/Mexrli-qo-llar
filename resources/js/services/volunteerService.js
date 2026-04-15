import api from './api'

export default {
    async createVolunteerApplication(payload) {
        const response = await api.post('/volunteer-applications', payload)
        return response.data.data ?? response.data
    },

    async getAll() {
        const response = await api.get('/admin/volunteer-applications')
        return response.data.data ?? response.data
    }
}
