import { formatAmount, formatMoneyAmount } from '@/utils/formatAmount'

export function useAdminUiHelpers() {
    const formatMoney = (value) => formatMoneyAmount(value, "so'm")

    const badgeClass = (status) => {
        const map = {
            completed: 'bg-green-50 text-green-700',
            success: 'bg-green-50 text-green-700',
            pending: 'bg-amber-50 text-amber-700',
            failed: 'bg-red-50 text-red-700',
            cancelled: 'bg-gray-100 text-gray-600',
            active: 'bg-green-50 text-green-700',
            inactive: 'bg-gray-100 text-gray-600',
        }

        return map[String(status || '').toLowerCase()] || 'bg-gray-100 text-gray-600'
    }

    return {
        formatMoney,
        formatAmount,
        badgeClass,
    }
}
