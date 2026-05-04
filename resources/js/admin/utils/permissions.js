const roleTabAccess = {
    super_admin: ['*'],
    editor: ['overview', 'cases', 'help-requests', 'volunteers', 'messages', 'blog', 'donations'],
    finance: ['overview', 'payments', 'donations'],
}

export const normalizeRole = (user) => {
    if (user?.role) return user.role
    if (user?.is_admin) return 'super_admin'
    return null
}

export const canAccessAdmin = (user) => Boolean(normalizeRole(user))

export const canAccessAdminTab = (user, tab) => {
    const role = normalizeRole(user)
    if (!role) return false

    const allowed = roleTabAccess[role] || []
    return allowed.includes('*') || allowed.includes(tab)
}
