import { ref } from 'vue'
import volunteerService from '@/services/volunteerService'

export function useVolunteerApplications() {
    const applications = ref([])
    const meta = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchApplications = async (params = {}) => {
        loading.value = true
        error.value = null
        const result = await volunteerService.fetchList(params)
        applications.value = result.data || []
        meta.value = result.meta
        error.value = result.error
        loading.value = false
    }

    const submitApplication = async (payload) => {
        loading.value = true
        error.value = null
        const result = await volunteerService.submit(payload)
        error.value = result.error
        loading.value = false
        return result
    }

    return { applications, meta, loading, error, fetchApplications, submitApplication }
}
