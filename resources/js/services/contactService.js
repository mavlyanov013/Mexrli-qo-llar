import api from './api'

export default {
    async sendContact(payload) {
        const response = await api.post('/contact-messages', payload)
        return response.data.data ?? response.data
    },

    async getAll() {
        const response = await api.get('/admin/contact-messages')
        return response.data.data ?? response.data
    }
}
