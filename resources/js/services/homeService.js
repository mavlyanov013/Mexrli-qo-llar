import api from './api'

export const getHomeData = async () => {
    const [cases, partners, posts, donations, volunteers] = await Promise.all([
        api.get('/cases'),
        api.get('/partners'),
        api.get('/blog-posts'),
        api.get('/donations'),
        api.get('/admin/volunteer-applications'),
    ])

    return {
        cases: cases.data.data ?? [],
        partners: partners.data.data ?? [],
        posts: posts.data.data ?? [],
        donations: donations.data.data ?? [],
        volunteers: volunteers.data.data ?? [],
    }
}
