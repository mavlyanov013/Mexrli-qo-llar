import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/authService'
import { canAccessAdmin } from '@/admin/utils/permissions'
import { clearAuthStorage, readStoredUser } from '@/utils/storage'

const user = ref(readStoredUser())

export function useAuth() {
    const router = useRouter()

    const login = async (payload) => {
        const res = await authService.loginUser(payload)

        localStorage.setItem('token', res.token)
        localStorage.setItem('user', JSON.stringify(res.user))

        user.value = res.user

        if (canAccessAdmin(res.user)) {
            router.push('/admin/dashboard')
        } else {
            router.push('/')
        }
    }

    const fetchUser = async () => {
        const token = localStorage.getItem('token')

        if (!token || token === 'null' || token === 'undefined') {
            user.value = null
            clearAuthStorage()
            throw new Error('Missing token')
        }

        try {
            const res = await authService.getMe()
            user.value = res.user ?? res
            localStorage.setItem('user', JSON.stringify(user.value))
            return user.value
        } catch (e) {
            user.value = null
            clearAuthStorage()
            throw e
        }
    }

    const logout = async () => {
        try {
            await authService.logoutUser()
        } catch (e) {}

        clearAuthStorage()
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
