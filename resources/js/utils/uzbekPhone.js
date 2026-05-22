export const UZ_PHONE_REGEX = /^\+998[0-9]{9}$/

export const UZ_PHONE_ERROR = "Telefon raqam +998 formatida bo'lishi kerak"

export const UZ_PHONE_PLACEHOLDER = '+998 XX XXX XX XX'

export function normalizeUzbekPhone(value) {
    const digits = String(value || '').replace(/\D/g, '')
    let local = digits

    if (local.startsWith('998')) {
        local = local.slice(3)
    }

    local = local.slice(0, 9)

    if (!local) {
        return ''
    }

    return `+998${local}`
}

export function formatUzbekPhoneDisplay(value) {
    const normalized = normalizeUzbekPhone(value)

    if (!normalized) {
        return ''
    }

    const local = normalized.slice(4)
    const parts = []

    if (local.length > 0) parts.push(local.slice(0, 2))
    if (local.length > 2) parts.push(local.slice(2, 5))
    if (local.length > 5) parts.push(local.slice(5, 7))
    if (local.length > 7) parts.push(local.slice(7, 9))

    return `+998${parts.length ? ` ${parts.join(' ')}` : ''}`
}

export function validateUzbekPhone(value, { required = true } = {}) {
    const normalized = normalizeUzbekPhone(value)

    if (!normalized) {
        return required ? UZ_PHONE_ERROR : null
    }

    return UZ_PHONE_REGEX.test(normalized) ? null : UZ_PHONE_ERROR
}
