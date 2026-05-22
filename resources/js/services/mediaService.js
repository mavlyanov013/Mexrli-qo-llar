import api from './api'
import { extractUploadedMedia, toServiceError } from './serviceHelpers'

const mediaService = {
    async upload(file, directory = 'admin') {
        try {
            const formData = new FormData()
            formData.append('file', file)
            formData.append('directory', directory)

            const response = await api.post('/admin/media', formData)

            return {
                data: extractUploadedMedia(response),
                error: null,
            }
        } catch (error) {
            return {
                data: null,
                error: toServiceError(error, 'Fayl yuklanmadi'),
            }
        }
    },

    async remove(path) {
        try {
            await api.delete('/admin/media', { data: { path } })
            return { error: null }
        } catch (error) {
            return { error: toServiceError(error, 'Fayl o‘chirilmadi') }
        }
    },
}

export default mediaService
