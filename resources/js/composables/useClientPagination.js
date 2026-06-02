import { computed, ref, unref, watch } from 'vue'

export function useClientPagination(source, perPage = 12) {
    const currentPage = ref(1)
    const pageSize = computed(() => Number(unref(perPage)) || 12)

    const items = computed(() => {
        const list = unref(source)
        return Array.isArray(list) ? list : []
    })

    const totalItems = computed(() => items.value.length)

    const totalPages = computed(() => {
        return Math.max(1, Math.ceil(totalItems.value / pageSize.value))
    })

    const paginatedItems = computed(() => {
        const start = (currentPage.value - 1) * pageSize.value
        return items.value.slice(start, start + pageSize.value)
    })

    const startItem = computed(() => {
        if (!totalItems.value) return 0
        return (currentPage.value - 1) * pageSize.value + 1
    })

    const endItem = computed(() => {
        return Math.min(currentPage.value * pageSize.value, totalItems.value)
    })

    const visiblePages = computed(() => {
        const pages = []
        const maxVisible = 5
        let start = Math.max(1, currentPage.value - 2)
        let end = Math.min(totalPages.value, start + maxVisible - 1)

        if (end - start + 1 < maxVisible) {
            start = Math.max(1, end - maxVisible + 1)
        }

        for (let i = start; i <= end; i += 1) {
            pages.push(i)
        }

        return pages
    })

    const goToPage = (page) => {
        const next = Number(page)
        if (!Number.isFinite(next)) return
        currentPage.value = Math.min(Math.max(1, next), totalPages.value)
    }

    const prevPage = () => {
        if (currentPage.value > 1) {
            currentPage.value -= 1
        }
    }

    const nextPage = () => {
        if (currentPage.value < totalPages.value) {
            currentPage.value += 1
        }
    }

    watch(items, () => {
        if (currentPage.value > totalPages.value) {
            currentPage.value = 1
        }
    })

    return {
        currentPage,
        totalPages,
        totalItems,
        paginatedItems,
        startItem,
        endItem,
        visiblePages,
        goToPage,
        prevPage,
        nextPage,
    }
}
