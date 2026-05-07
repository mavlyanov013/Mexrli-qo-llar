import { ref } from 'vue'
import contactService from '@/services/contactService'

export function useMessages() {
    const messages = ref([])
    const meta = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchMessages = async (params = {}) => {
        loading.value = true
        error.value = null
        const result = await contactService.fetchList(params)
        messages.value = result.data || []
        meta.value = result.meta
        error.value = result.error
        loading.value = false
    }

    const updateMessage = async (id, payload) => contactService.update(id, payload)
    const deleteMessage = async (id) => contactService.remove(id)

    return { messages, meta, loading, error, fetchMessages, updateMessage, deleteMessage }
}
