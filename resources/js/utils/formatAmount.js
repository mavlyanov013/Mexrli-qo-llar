/**
 * Raqamlarni minglik ajratgich sifatida probel bilan formatlaydi: 1 500 000
 */
export function formatAmount(value) {
    const num = Number(value)

    if (!Number.isFinite(num)) {
        return '0'
    }

    const negative = num < 0
    const [intPart, decPart] = Math.abs(num).toString().split('.')
    const grouped = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
    const body = decPart ? `${grouped}.${decPart}` : grouped

    return negative ? `-${body}` : body
}

export function formatMoneyAmount(value, suffix = '') {
    const amount = formatAmount(value)

    return suffix ? `${amount} ${suffix}`.trim() : amount
}
