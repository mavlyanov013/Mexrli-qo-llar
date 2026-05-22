import api from './api'

export default {
    async fetchToday() {
        const response = await api.get('/stats/today')
        return response.data?.data ?? response.data
    },
}
