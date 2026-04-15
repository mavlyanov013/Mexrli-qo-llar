import api from './api'

export const getHomePage = async () => {
    const response = await api.get('/home')
    return response.data
}
