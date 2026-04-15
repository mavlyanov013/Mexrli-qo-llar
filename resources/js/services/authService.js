import api from './api'

export default {
    async loginUser(payload) {
        const response = await api.post('/auth/login', payload)
        return response.data
    },

    async getMe() {
        const response = await api.get('/auth/me')
        return response.data
    },

    async logoutUser() {
        const response = await api.post('/auth/logout')
        return response.data
    }
}
