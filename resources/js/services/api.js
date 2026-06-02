import axios from 'axios'
import { clearAuthStorage } from '@/utils/storage'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v1',
    headers: {
        Accept: 'application/json'
    }
})

const isValidToken = (token) =>
    typeof token === 'string' && token.length > 20 && token !== 'null' && token !== 'undefined'

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    const lang = localStorage.getItem('lang') || 'uz'

    if (isValidToken(token)) {
        config.headers.Authorization = `Bearer ${token}`
    }

    config.headers['X-Locale'] = lang

    return config
})

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status
        const url = error.config?.url || ''

        if (status === 401 && !url.includes('/auth/login')) {
            clearAuthStorage()

            if (!window.location.pathname.startsWith('/login')) {
                window.location.assign('/login?session=expired')
            }
        }

        return Promise.reject(error)
    }
)
export default api
