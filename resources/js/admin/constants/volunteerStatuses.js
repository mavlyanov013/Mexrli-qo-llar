export const VOLUNTEER_STATUSES = {
    tasdiqlandi: { label: 'Tasdiqlandi', tone: 'success' },
    rad_etildi: { label: 'Rad etildi', tone: 'danger' },
    rezerv: { label: 'Rezerv', tone: 'info' },
    pending: { label: 'Rezerv', tone: 'info' },
    new: { label: 'Rezerv', tone: 'info' },
    reviewed: { label: 'Rezerv', tone: 'info' },
    accepted: { label: 'Tasdiqlandi', tone: 'success' },
    approved: { label: 'Tasdiqlandi', tone: 'success' },
    rejected: { label: 'Rad etildi', tone: 'danger' },
}

export const VOLUNTEER_STATUS_OPTIONS = [
    { value: 'tasdiqlandi', label: 'Tasdiqlandi' },
    { value: 'rad_etildi', label: 'Rad etildi' },
    { value: 'rezerv', label: 'Rezerv' },
]

export function normalizeVolunteerStatus(status) {
    const value = String(status || '').toLowerCase()

    if (VOLUNTEER_STATUS_OPTIONS.some((option) => option.value === value)) {
        return value
    }

    const legacyMap = {
        pending: 'rezerv',
        new: 'rezerv',
        reviewed: 'rezerv',
        accepted: 'tasdiqlandi',
        approved: 'tasdiqlandi',
        rejected: 'rad_etildi',
    }

    return legacyMap[value] || 'rezerv'
}
