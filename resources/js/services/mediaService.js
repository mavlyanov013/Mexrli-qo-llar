    import api from './api'
    import { normalizeItem, toServiceError } from './serviceHelpers'

    const mediaService = {
        async upload(file, directory = 'admin') {
            const formData = new FormData()
            formData.append('file', file)
            formData.append('directory', directory)

            const response = await api.post('/admin/media', formData)

            return {
                data: response.data,
                error: null
            }
        },

        async remove(path) {
            try {
                await api.delete('/admin/media', { data: { path } })
                return { error: null }
            } catch (error) {
                return { error: toServiceError(error, 'Failed to delete file') }
            }
        },
    }

    export default mediaService
