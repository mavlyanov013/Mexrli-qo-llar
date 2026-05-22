import { transliterateLatinToCyrillic } from './uzbekTransliterate'

export const LOCALES = [
    { key: 'uz', label: 'UZ' },
    { key: 'oz', label: 'ЎЗ' },
    { key: 'ru', label: 'RU' },
]

export function localeToSuffix(locale) {
    if (locale === 'ru') return 'ru'
    if (locale === 'uz_cyrl' || locale === 'oz') return 'oz'
    return 'uz'
}

export function pickLocalized(item, field, locale) {
    if (!item) return ''

    const suffix = localeToSuffix(locale)
    const localized = item[`${field}_${suffix}`]
    const latin = item[`${field}_uz`]
    const legacy = item[field]

    if (localized) return localized

    if (suffix === 'ru') {
        return legacy || ''
    }

    const fallbackLatin = latin || legacy || ''

    if (suffix === 'oz' && fallbackLatin) {
        return transliterateLatinToCyrillic(fallbackLatin)
    }

    return fallbackLatin
}

export function emptyLocalizedFields(fields) {
    return fields.reduce((acc, field) => {
        acc[`${field}_uz`] = ''
        acc[`${field}_oz`] = ''
        acc[`${field}_ru`] = ''
        return acc
    }, {})
}

export function assignLocalizedFromRow(target, row, fields) {
    fields.forEach((field) => {
        target[`${field}_uz`] = row?.[`${field}_uz`] ?? row?.[field] ?? ''
        target[`${field}_oz`] = row?.[`${field}_oz`] ?? ''
        target[`${field}_ru`] = row?.[`${field}_ru`] ?? ''
    })
}

export const ADMIN_FIELD_LABELS = {
    name: 'Ism',
    location: 'Joylashuv',
    condition: 'Kasallik / holat',
    short_description: 'Qisqa tavsif',
    story: 'Hikoya',
    title: 'Sarlavha',
    excerpt: 'Qisqa matn',
    content: 'To‘liq matn',
    description: 'Tavsif',
    question: 'Savol',
    answer: 'Javob',
    bank: 'Bank nomi',
    org_name: 'Tashkilot nomi',
    legal_address: 'Yuridik manzil',
    position: 'Lavozim',
}

/** Admin: faqat o‘zbek (lotin) maydonlarini tekshiradi */
export function validateAdminLocalizedFields(form, fields) {
    const missing = []

    fields.forEach((field) => {
        if (!String(form[`${field}_uz`] || '').trim()) {
            missing.push(ADMIN_FIELD_LABELS[field] || field)
        }
    })

    return missing
}

/** Saqlashdan oldin: kirill lotindan; rus alohida kiritilgan bo‘lsa saqlanadi */
export function applyAdminLocalization(form, fields) {
    fields.forEach((field) => {
        const uz = String(form[`${field}_uz`] || '').trim()
        const ru = String(form[`${field}_ru`] || '').trim()

        form[`${field}_uz`] = uz
        form[`${field}_oz`] = transliterateLatinToCyrillic(uz)
        form[`${field}_ru`] = ru || null
    })
}

/** Admin API: lotin + avtomatik kirill + ixtiyoriy rus */
export function buildAdminPayload(form, fields, extra = {}) {
    applyAdminLocalization(form, fields)

    return { ...form, ...extra }
}

export function validateLocalizedFields(form, fields) {
    const missing = []

    fields.forEach((field) => {
        LOCALES.forEach(({ key, label }) => {
            const value = form[`${field}_${key}`]
            if (!String(value || '').trim()) {
                missing.push(`${field} (${label})`)
            }
        })
    })

    return missing
}
