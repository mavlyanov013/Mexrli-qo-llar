export const PAYMENT_PROVIDERS = {
    paycom: 'paycom',
    click: 'click',
    paynet: 'paynet',
    uzumbank: 'uzumbank',
}

export const ONLINE_PAYMENT_PROVIDER_OPTIONS = [
    { value: 'paycom', label: 'Payme' },
    { value: 'click', label: 'Click' },
    { value: 'paynet', label: 'Paynet' },
    { value: 'uzumbank', label: 'Uzum Bank' },
]

export const PAYMENT_STATUS_FILTER_OPTIONS = [
    { value: 'pending', label: 'Kutilmoqda' },
    { value: 'success', label: 'Muvaffaqiyatli' },
    { value: 'completed', label: 'Yakunlangan' },
    { value: 'failed', label: 'Muvaffaqiyatsiz' },
    { value: 'cancelled', label: 'Bekor qilingan' },
]

export function providerLabel(provider) {
    const key = String(provider || '').toLowerCase()
    return ONLINE_PAYMENT_PROVIDER_OPTIONS.find((item) => item.value === key)?.label || provider || '-'
}
