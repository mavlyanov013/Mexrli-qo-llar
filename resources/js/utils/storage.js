export function readStoredUser() {
    try {
        const raw = localStorage.getItem('user')
        if (!raw || raw === 'null' || raw === 'undefined') {
            return null
        }

        return JSON.parse(raw)
    } catch {
        localStorage.removeItem('user')
        return null
    }
}

export function clearAuthStorage() {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
}
