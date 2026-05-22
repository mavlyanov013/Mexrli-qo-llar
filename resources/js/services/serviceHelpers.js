export const normalizeList = (response) => response?.data?.data ?? response?.data ?? []

export const normalizeItem = (response) => response?.data?.data ?? response?.data ?? null

export const normalizeMeta = (response) => response?.data?.meta ?? null

export const toServiceError = (error, fallbackMessage = 'Unexpected API error') => {
    const message = error?.response?.data?.message || error?.message || fallbackMessage
    const status = error?.response?.status || 500
    return { message, status, raw: error }
}

/** Axios javobidan media upload URL/path ajratadi */
export const extractUploadedMedia = (response) => {
    const body = response?.data ?? response ?? {}
    const payload = body?.data ?? body

    return {
        path: payload?.path ?? null,
        url: payload?.url ?? null,
    }
}
