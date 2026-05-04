import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/authService'

const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))

export function useAuth() {
    const router = useRouter()

    const login = async (payload) => {
        const res = await authService.loginUser(payload)

        localStorage.setItem('token', res.token)
        localStorage.setItem('user', JSON.stringify(res.user))

        user.value = res.user

        if (res.user?.is_admin) {
            router.push('/admin/dashboard')
        } else {
            router.push('/')
        }
    }

    const fetchUser = async () => {
        try {
            const res = await authService.getMe()
            user.value = res.user ?? res
            localStorage.setItem('user', JSON.stringify(user.value))
        } catch (e) {
            user.value = null
        }
    }

    const logout = async () => {
        try {
            await authService.logoutUser()
        } catch (e) {}

        localStorage.removeItem('token')
        localStorage.removeItem('user')
        user.value = null

        router.push('/login')
    }

    return {
        user,
        login,
        fetchUser,
        logout,
    }
}
