export const PAYMENT_STATUSES = {
    pending: { label: 'Kutilmoqda', tone: 'warning' },
    success: { label: 'Muvaffaqiyatli', tone: 'success' },
    completed: { label: 'Yakunlangan', tone: 'success' },
    failed: { label: 'Muvaffaqiyatsiz', tone: 'danger' },
    cancelled: { label: 'Bekor qilingan', tone: 'danger' },
    funded: { label: 'Moliyalashtirilgan', tone: 'info' },
}

export const CASE_STATUSES = {
    new: { labelKey: 'common.status.new', tone: 'info' },
    draft: { labelKey: 'common.status.draft', tone: 'info' },
    active: { labelKey: 'common.status.active', tone: 'success' },
    paused: { labelKey: 'common.status.paused', tone: 'warning' },
    completed: { labelKey: 'common.status.completed', tone: 'success' },
    closed: { labelKey: 'common.status.closed', tone: 'danger' },
}

export const DONATION_STATUSES = {
    pending: { labelKey: 'common.status.pending', tone: 'warning' },
    success: { labelKey: 'common.status.success', tone: 'success' },
    completed: { labelKey: 'common.status.success', tone: 'success' },
    failed: { labelKey: 'common.status.failed', tone: 'danger' },
}

export const VOLUNTEER_STATUSES = {
    tasdiqlandi: { label: 'Tasdiqlandi', tone: 'success' },
    rad_etildi: { label: 'Rad etildi', tone: 'danger' },
    rezerv: { label: 'Rezerv', tone: 'info' },
}

export const MESSAGE_STATUSES = {
    new: { labelKey: 'common.status.new', tone: 'info' },
    read: { labelKey: 'common.status.read', tone: 'warning' },
    replied: { labelKey: 'common.status.replied', tone: 'success' },
}
export const ACTIVE_STATUSES = {
    success: 'Faol',
    pending: 'Faol emas'
}
export const HELP_REQUEST_STATUS = {
    pending: { label: 'Kutilmoqda', tone: 'warning' },
    rezerv: { label: 'Rezerv', tone: 'info' },
    tasdiqlandi: { label: 'Tasdiqlandi', tone: 'success' },
    rad_etildi: { label: 'Rad etildi', tone: 'danger' },
    approved: { label: 'Tasdiqlandi', tone: 'success' },
    rejected: { label: 'Rad etildi', tone: 'danger' },
}

export const HELP_REQUEST_STATUS_OPTIONS = [
    { value: 'pending', label: 'Kutilmoqda' },
    { value: 'rezerv', label: 'Rezerv' },
    { value: 'tasdiqlandi', label: 'Tasdiqlandi' },
    { value: 'rad_etildi', label: 'Rad etildi' },
]
export const HELP_REQUEST_CATEGORIES = {
    medical_treatment: 'Tibbiy davolanish',
    surgery: 'Jarrohlik',
    rehabilitation: 'Reabilitatsiya',
    medication: 'Dori-darmon',
    family_support: 'Oilaviy yordam',
    other: 'Boshqa',
}
export const CASE_STATUS_OPTIONS = ['new','draft','active','paused','completed','closed']
export const PAYMENT_STATUS_OPTIONS = ['pending', 'success', 'failed', 'cancelled', 'completed']
export const VOLUNTEER_STATUS_OPTIONS = ['tasdiqlandi', 'rad_etildi', 'rezerv']
