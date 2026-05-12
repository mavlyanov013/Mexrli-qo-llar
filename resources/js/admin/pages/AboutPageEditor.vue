<template>
    <AdminCrudShell title="About sahifasi bo‘limlari">
        <div class="mb-4">
            <button class="btn-primary" @click="openCreate">➕ Yangi bo‘lim qo‘shish</button>
        </div>

        <div v-if="showForm" class="bg-white p-5 rounded-xl shadow mb-6">
            <h2 class="font-bold mb-4">{{ editingId ? 'Bo‘limni tahrirlash' : 'Bo‘lim yaratish' }}</h2>
            <select v-model="form.type" class="input mb-3">
                <option value="hero">Hero</option>
                <option value="value">Qadriyat</option>
                <option value="doc">Hujjat</option>
                <option value="team">Jamoa</option>
                <option value="legal">Yuridik</option>
            </select>
            <input v-model="form.title" placeholder="Sarlavha" class="input mb-3" />
            <input v-model="form.subtitle" placeholder="Sub sarlavha" class="input mb-3" />
            <textarea v-model="form.content" placeholder="Matn" class="input mb-3"></textarea>
            <textarea v-model="extraJson" placeholder='{"icon":"Eye","tone":"blue"}' class="input mb-3"></textarea>
            <div class="flex gap-3">
                <button @click="saveSection" class="btn-primary">Saqlash</button>
                <button @click="closeForm" class="btn-secondary">Bekor qilish</button>
            </div>
        </div>

        <div class="space-y-3">
            <div v-for="(s, idx) in sections" :key="s.id" class="bg-white p-4 rounded border">
                <div class="flex justify-between gap-2 items-start">
                    <div>
                        <b>{{ s.type }}</b> - {{ s.title }}
                        <p class="text-sm text-gray-500">{{ s.content }}</p>
                    </div>
                    <div class="flex gap-2 text-sm">
                        <button class="text-gray-500" :disabled="idx === 0" @click="move(idx, -1)">↑</button>
                        <button class="text-gray-500" :disabled="idx === sections.length - 1" @click="move(idx, 1)">↓</button>
                        <button @click="openEdit(s)" class="text-amber-600">✏️ Tahrirlash</button>
                        <button @click="remove(s.id)" class="text-red-500">🗑 O‘chirish</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminCrudShell>
</template>

<script setup>
import {ref, onMounted, computed} from 'vue'
import api from '@/services/api'
import AdminCrudShell from "../components/common/AdminCrudShell.vue";

const sections = ref([])
const pageId = ref(null)
const editingId = ref(null)
const showForm = ref(false)

const form = ref({
    page_id: null,
    type: 'value',
    title: '',
    subtitle: '',
    content: '',
})

const extraJson = ref('{}')

const load = async () => {
    const pagesRes = await api.get('/admin/pages')
    const about = (pagesRes.data.data ?? []).find((item) => item.slug === 'about')
    if (!about) return
    pageId.value = about.id
    form.value.page_id = about.id
    const res = await api.get(`/admin/pages/${about.id}`)
    sections.value = res.data.data.sections
}

const resetForm = () => {
    editingId.value = null
    form.value = { page_id: pageId.value, type: 'value', title: '', subtitle: '', content: '' }
    extraJson.value = '{}'
}
const values = computed(() =>
    sections.value
        .filter(s => s.type === 'value')
        .map(s => ({
            title: s.title,
            desc: s.content,
            icon: s.extra?.icon || 'Heart',
            tone: s.extra?.tone || 'blue'
        }))
)
const team = computed(() =>
    sections.value
        .filter(s => s.type === 'team')
        .map(s => ({
            name: s.title,
            role: s.subtitle,
            initials: s.title.split(' ').map(n => n[0]).join('')
        }))
)
const docs = computed(() =>
    sections.value
        .filter(s => s.type === 'doc')
        .map(s => ({
            title: s.title,
            desc: s.content,
            href: s.extra?.href || '#'
        }))
)

const openCreate = () => {
    resetForm()
    showForm.value = true
}

const openEdit = (section) => {
    editingId.value = section.id
    showForm.value = true
    form.value = {
        page_id: pageId.value,
        type: section.type || 'value',
        title: section.title || '',
        subtitle: section.subtitle || '',
        content: section.content || '',
    }
    extraJson.value = JSON.stringify(section.extra || {}, null, 2)
}

const closeForm = () => {
    showForm.value = false
    resetForm()
}

const saveSection = async () => {
    const payload = { ...form.value, extra: JSON.parse(extraJson.value || '{}') }
    if (editingId.value) await api.put(`/admin/sections/${editingId.value}`, payload)
    else await api.post('/admin/sections', payload)
    closeForm()
    await load()
}

const remove = async (id) => {
    await api.delete(`/admin/sections/${id}`)
    await load()
}

const move = async (index, direction) => {
    const items = [...sections.value]
    const target = index + direction
    const temp = items[index]
    items[index] = items[target]
    items[target] = temp

    const payload = items.map((item, sortIndex) => ({ id: item.id, sort_order: sortIndex }))
    await api.post('/admin/sections/reorder', { items: payload })
    await load()
}

onMounted(load)
</script>

<style scoped>
.input { width: 100%; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 12px; }
.btn-primary { background: #2A7DE1; color: #fff; border-radius: 12px; padding: 10px 18px; }
.btn-secondary { border: 1px solid #ddd; border-radius: 12px; padding: 10px 18px; }
</style>
