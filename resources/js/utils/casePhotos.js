import { resolveMediaUrl } from './mediaUrl'

export function getCasePhotoItems(caseData) {
    const photos = Array.isArray(caseData?.photos) ? caseData.photos : []

    if (photos.length) {
        return photos
            .map((photo, index) => {
                const url = resolveMediaUrl(typeof photo === 'string' ? photo : photo?.url)

                if (!url) return null

                return {
                    url,
                    name: typeof photo === 'object' && photo?.name ? photo.name : `Rasm ${index + 1}`,
                }
            })
            .filter(Boolean)
    }

    const cover = resolveMediaUrl(caseData?.photo_url)

    if (!cover) return []

    return [{ url: cover, name: 'Asosiy rasm' }]
}

export function getCaseCoverPhoto(caseData) {
    return getCasePhotoItems(caseData)[0]?.url || ''
}
