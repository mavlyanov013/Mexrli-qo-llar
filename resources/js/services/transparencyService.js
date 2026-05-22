import api from './api'

export default {
    async fetchChart(year) {
        const response = await api.get('/transparency/chart', {
            params: { year },
        })

        return response.data?.data ?? response.data
    },
}
