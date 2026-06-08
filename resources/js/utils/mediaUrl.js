/**
 * Normalize uploaded media paths for display in the browser.
 */
export function resolveMediaUrl(value) {
    if (!value) return ''

    const raw = String(value).trim()

    if (raw.startsWith('http://') || raw.startsWith('https://') || raw.startsWith('/')) {
        try {
            const parsed = new URL(raw, window.location.origin)
            if (parsed.pathname.startsWith('/storage/')) {
                return parsed.pathname
            }
        } catch {
            // keep raw value
        }

        return raw
    }

    return `/storage/${raw.replace(/^\/+/, '')}`
}
