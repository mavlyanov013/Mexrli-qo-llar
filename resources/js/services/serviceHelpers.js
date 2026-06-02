export const normalizeList = (response) => response?.data?.data ?? response?.data ?? []

export const normalizeItem = (response) => response?.data?.data ?? response?.data ?? null

export const normalizeMeta = (response) => response?.data?.meta ?? null

export const normalizePaginationHeaders = (response) => {
    const headers = response?.headers || {}

    const currentPage = Number(headers['x-pagination-current-page'])
    const lastPage = Number(headers['x-pagination-last-page'])
    const perPage = Number(headers['x-pagination-per-page'])
    const total = Number(headers['x-pagination-total'])

    if (!currentPage && !lastPage && !total) {
        return null
    }

    return {
        current_page: currentPage || 1,
        last_page: lastPage || 1,
        per_page: perPage || 20,
        total: total || 0,
    }
}

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
