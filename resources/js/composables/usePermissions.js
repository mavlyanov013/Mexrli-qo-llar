import { computed } from 'vue'
import { useAuth } from './useAuth'
import { normalizeRole } from '@/admin/utils/permissions'

export function usePermissions() {
    const { user } = useAuth()

    const role = computed(() => normalizeRole(user.value))
    const isSuperAdmin = computed(() => role.value === 'super_admin')
    const isEditor = computed(() => role.value === 'editor')

    return {
        role,
        isSuperAdmin,
        isEditor,
    }
}
