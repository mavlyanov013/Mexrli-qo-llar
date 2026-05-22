import api from './api'
import mediaService from './mediaService'

export default {
    getContent() {
        return api.get('/about/content').then((r) => r.data.data ?? r.data)
    },

    getAdminContent() {
        return api.get('/admin/about/content').then((r) => r.data)
    },

    saveBank(payload) {
        return api.put('/admin/about/bank-details', payload).then((r) => r.data)
    },

    saveLegal(payload) {
        return api.put('/admin/about/legal-info', payload).then((r) => r.data)
    },

    saveDocument(key, payload) {
        return api.put(`/admin/about/documents/${key}`, payload).then((r) => r.data)
    },

    async uploadDocumentFile(file) {
        const { data, error } = await mediaService.upload(file, 'about')
        if (error) {
            throw new Error(error.message)
        }

        return {
            url: data?.url ?? null,
            path: data?.path ?? null,
        }
    },

    createTeamMember(payload) {
        return api.post('/admin/about/team-members', payload).then((r) => r.data)
    },

    updateTeamMember(id, payload) {
        return api.put(`/admin/about/team-members/${id}`, payload).then((r) => r.data)
    },

    removeTeamMember(id) {
        return api.delete(`/admin/about/team-members/${id}`).then((r) => r.data)
    },

    async uploadTeamPhoto(file) {
        const { data, error } = await mediaService.upload(file, 'team')
        if (error) {
            throw new Error(error.message)
        }

        return data?.url ?? null
    },
}
