import { useI18n } from 'vue-i18n'
import { formatAmount, formatMoneyAmount } from '@/utils/formatAmount'

export function useFormatMoney() {
    const { t } = useI18n()

    const formatMoney = (value, options = {}) => {
        const suffix = options.suffix
            ?? (options.useCurrencyCode ? t('common.currencyCode') : t('public.donate.sumSuffix'))

        return formatMoneyAmount(value, suffix)
    }

    const formatNumber = (value) => formatAmount(value)

    return {
        formatMoney,
        formatNumber,
        formatAmount,
    }
}
