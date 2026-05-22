import { useI18n } from 'vue-i18n'
import { pickLocalized } from '@/utils/localizedContent'

export function useLocalizedDisplay() {
    const { locale } = useI18n()

    const content = (item, field) => pickLocalized(item, field, locale.value)

    return {
        locale,
        content,
    }
}
