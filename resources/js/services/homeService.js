import api from './api'

export const getHomeData = async () => {
    const [casesRes, partnersRes, postsRes, donationsRes] = await Promise.all([
        api.get('/cases'),
        api.get('/partners'),
        api.get('/news', { params: { published_only: true, per_page: 12 } }),
        api.get('/donations'),
    ])

    return {
        cases: casesRes.data.data ?? [],
        partners: partnersRes.data.data ?? [],
        posts: postsRes.data.data ?? [],
        donations: donationsRes.data.data ?? [],
        volunteers: [],
    }
}
