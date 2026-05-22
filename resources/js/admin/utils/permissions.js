const roleTabAccess = {
    super_admin: ['*'],
    admin: ['*'],
    editor: ['overview', 'cases', 'help-requests', 'volunteers', 'messages', 'blog', 'news', 'faq', 'about-sections', 'contact-info', 'pages', 'treatment-processes', 'partners'],
}

export const normalizeRole = (user) => {
    if (!user) return null
    if (user.role === 'admin') return 'super_admin'
    if (user.role === 'super_admin' || user.role === 'editor') return user.role
    if (user.is_admin) return 'super_admin'
    return null
}

export const canAccessAdmin = (user) => {
    const role = normalizeRole(user)
    return role === 'super_admin' || role === 'editor'
}

export const canAccessAdminTab = (user, tab) => {
    const role = normalizeRole(user)
    if (!role) return false

    const allowed = roleTabAccess[role] || []
    return allowed.includes('*') || allowed.includes(tab)
}

export const canAccessAdminRoute = (user, path) => {
    const match = path.match(/^\/admin(?:\/([^/]+))?/) || []
    const tab = match[1] || 'dashboard'
    return canAccessAdminTab(user, tab === 'dashboard' ? 'overview' : tab)
}
