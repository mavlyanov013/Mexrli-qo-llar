/**
 * CSV export utilities (Excel-compatible UTF-8 with BOM).
 */

export function escapeCsvValue(value) {
    if (value === null || value === undefined) {
        return ''
    }

    const str = String(value).replace(/"/g, '""')

    if (/[",\n\r]/.test(str)) {
        return `"${str}"`
    }

    return str
}

export function buildCsv(headers, rows) {
    const lines = [headers.map(escapeCsvValue).join(',')]

    for (const row of rows) {
        lines.push(row.map(escapeCsvValue).join(','))
    }

    return `\uFEFF${lines.join('\r\n')}`
}

export function downloadCsv(filename, csvContent) {
    const safeName = filename.endsWith('.csv') ? filename : `${filename}.csv`
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')

    link.href = url
    link.download = safeName
    link.style.display = 'none'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
}

/**
 * @param {object} options
 * @param {string} options.filename
 * @param {Array<{ key: string, label: string }>} options.columns
 * @param {Array<object>} options.rows
 * @param {(row: object, key: string) => unknown} [options.getCellValue]
 */
export function exportTableToCsv({ filename, columns, rows, getCellValue }) {
    const exportColumns = columns.filter((column) => column.key !== 'actions')
    const headers = exportColumns.map((column) => column.label)
    const dataRows = rows.map((row) => (
        exportColumns.map((column) => {
            const value = getCellValue
                ? getCellValue(row, column.key)
                : row[column.key]

            return value ?? ''
        })
    ))

    downloadCsv(filename, buildCsv(headers, dataRows))
}

/**
 * Fetch all pages from a paginated admin API.
 *
 * @param {(params: object) => Promise<{ data: Array, meta: object|null }>} fetchFn
 * @param {object} [baseParams]
 * @param {number} [perPage]
 */
export async function fetchAllPaginatedResults(fetchFn, baseParams = {}, perPage = 200) {
    const items = []
    let page = 1
    let lastPage = 1

    while (page <= lastPage && page <= 100) {
        const result = await fetchFn({ ...baseParams, page, per_page: perPage })
        const chunk = Array.isArray(result?.data) ? result.data : []

        items.push(...chunk)

        lastPage = result?.meta?.last_page || page

        if (chunk.length === 0 || page >= lastPage) {
            break
        }

        page += 1
    }

    return items
}

/**
 * Try backend export URL; fall back to client-side CSV from rows.
 */
export async function downloadExport({
    filename,
    columns,
    rows,
    getCellValue,
    fetchAllRows,
    exportUrl,
}) {
    if (exportUrl) {
        try {
            const response = await fetch(exportUrl, {
                headers: {
                    Accept: 'text/csv, application/json',
                    Authorization: localStorage.getItem('token')
                        ? `Bearer ${localStorage.getItem('token')}`
                        : '',
                },
            })

            if (response.ok) {
                const blob = await response.blob()
                const url = URL.createObjectURL(blob)
                const link = document.createElement('a')
                link.href = url
                link.download = filename.endsWith('.csv') ? filename : `${filename}.csv`
                link.click()
                URL.revokeObjectURL(url)
                return
            }
        } catch {
            // Fall through to client-side export.
        }
    }

    const data = typeof fetchAllRows === 'function'
        ? await fetchAllRows()
        : rows

    exportTableToCsv({
        filename,
        columns,
        rows: data,
        getCellValue,
    })
}
