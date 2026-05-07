import { ref } from 'vue'
import volunteerService from '@/services/volunteerService'

export function useVolunteers() {
    const volunteers = ref([])
    const meta = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchVolunteers = async (params = {}) => {
        loading.value = true
        error.value = null
        const result = await volunteerService.fetchList(params)
        volunteers.value = result.data || []
        meta.value = result.meta
        error.value = result.error
        loading.value = false
    }

    const updateVolunteer = async (id, payload) => volunteerService.update(id, payload)
    const deleteVolunteer = async (id) => volunteerService.remove(id)
    const submitVolunteer = async (payload) => volunteerService.submit(payload)

    return { volunteers, meta, loading, error, fetchVolunteers, updateVolunteer, deleteVolunteer, submitVolunteer }
}
