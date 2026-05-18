<template>
    <AdminCrudShell title="About sahifasi bo‘limlari">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Bo‘limlar</h2>

            <button class="btn-primary flex items-center gap-2" @click="openCreate">
                <Plus class="w-4 h-4" />
                Yangi bo‘lim
            </button>
        </div>

        <!-- FORM -->
        <div v-if="showForm" class="card mb-6 space-y-4">

            <div class="flex justify-between items-center">
                <h3 class="font-semibold">
                    {{ editingId ? 'Tahrirlash' : 'Yangi bo‘lim' }}
                </h3>

                <button @click="closeForm" class="text-gray-400 hover:text-red-500">
                    ✕
                </button>
            </div>

            <!-- TYPE -->
            <div>
                <label class="label">Bo‘lim turi</label>
                <select v-model="form.type" class="input">
                    <option value="hero">Hero</option>
                    <option value="value">Value</option>
                    <option value="doc">Document</option>
                    <option value="team">Team</option>
                    <option value="legal">Legal</option>
                </select>
            </div>

            <!-- TITLE -->
            <div>
                <label class="label">Sarlavha</label>
                <input v-model="form.title" class="input" />
            </div>

            <!-- SUBTITLE -->
            <div>
                <label class="label">Subtitle</label>
                <input v-model="form.subtitle" class="input" />
            </div>

            <!-- CONTENT -->
            <div>
                <label class="label">Content</label>
                <textarea v-model="form.content" class="input"></textarea>
            </div>

            <!-- IMAGE UPLOAD -->
            <div>
                <label class="label">Rasm (optional)</label>

                <div class="upload-box" @click="$refs.image.click()">
                    <input ref="image" type="file" hidden @change="uploadImage" />

                    <div v-if="!preview">
                        📷 Rasm yuklash
                    </div>

                    <img v-else :src="preview" class="preview-img" />
                </div>
            </div>

            <!-- DOC UPLOAD -->
            <div v-if="form.type === 'doc'" class="card">

                <div class="flex items-center justify-between mb-3">
                    <label class="label flex items-center gap-2">
                        <FileText class="w-4 h-4 text-blue-600" />
                        Hujjat
                    </label>
                </div>

                <div
                    class="doc-upload"
                    @click="$refs.doc.click()"
                >
                    <input ref="doc" type="file" hidden @change="uploadDoc" />

                    <div v-if="!form.file_name" class="text-gray-500">
                        📎 Hujjat yuklash uchun bosing
                    </div>

                    <div v-else class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <FileText class="w-5 h-5 text-green-600" />
                            <span class="font-medium">{{ form.file_name }}</span>
                        </div>

                        <span class="text-xs text-green-600">Yuklangan ✓</span>
                    </div>
                </div>

                <!-- link preview -->
                <a
                    v-if="form.file_path"
                    :href="form.file_path"
                    target="_blank"
                    class="text-sm text-blue-600 mt-2 block"
                >
                    🔗 Hujjatni ochish
                </a>

            </div>

            <!-- EXTRA JSON -->
            <div>
                <label class="label">Extra JSON</label>
                <textarea v-model="extraJson" class="input font-mono"></textarea>
            </div>

            <!-- ACTIONS -->
            <div class="flex justify-end gap-3">
                <button class="btn-secondary" @click="closeForm">Bekor qilish</button>
                <button class="btn-primary" @click="saveSection">Saqlash</button>
            </div>
        </div>

        <!-- LIST -->
        <div class="grid gap-3">
            <div v-for="(s, idx) in sections" :key="s.id" class="card flex justify-between">

                <!-- LEFT -->
                <div class="flex gap-3 items-start">

                    <div class="icon-box">
                        <component :is="getIcon(s.type)" class="w-5 h-5" />
                    </div>

                    <div>
                        <div class="font-semibold">
                            {{ s.title }}
                        </div>

                        <div class="text-xs text-gray-500">
                            {{ s.type }}
                        </div>

                        <p class="text-sm text-gray-600 mt-1">
                            {{ s.content }}
                        </p>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex gap-2 items-start">

                    <button class="icon-btn" @click="move(idx,-1)">↑</button>
                    <button class="icon-btn" @click="move(idx,1)">↓</button>

                    <button class="icon-btn text-amber-500" @click="openEdit(s)">
                        <Pencil class="w-4 h-4" />
                    </button>

                    <button class="icon-btn text-red-500" @click="remove(s.id)">
                        <Trash2 class="w-4 h-4" />
                    </button>

                </div>
            </div>
        </div>

    </AdminCrudShell>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { Plus, Pencil, Trash2, FileText, Users, Shield, Eye, Heart } from 'lucide-vue-next'
import AdminCrudShell from "../components/common/AdminCrudShell.vue";

const sections = ref([])
const showForm = ref(false)
const editingId = ref(null)
const pageId = ref(null)

const form = ref({
    page_id: null,
    type: 'hero',
    title: '',
    subtitle: '',
    content: '',
    image: '',
    file_path: '',
    file_name: ''
})

const extraJson = ref('{}')
const preview = ref('')

const load = async () => {
    const res = await api.get('/admin/pages')
    const about = res.data.data.find(p => p.slug === 'about')

    pageId.value = about.id
    form.value.page_id = about.id

    const page = await api.get(`/admin/pages/${about.id}`)
    sections.value = page.data.data.sections
}

const openCreate = () => {
    editingId.value = null
    showForm.value = true
}

const openEdit = (s) => {
    editingId.value = s.id
    showForm.value = true

    form.value = {
        page_id: pageId.value,
        type: s.type,
        title: s.title,
        subtitle: s.subtitle,
        content: s.content,
        image: s.image,
        file_path: s.file_path,
        file_name: s.file_name
    }

    preview.value = s.image || ''
    extraJson.value = JSON.stringify(s.extra || {}, null, 2)
}

const closeForm = () => {
    showForm.value = false
}

const uploadImage = async (e) => {
    const file = e.target.files[0]
    const url = URL.createObjectURL(file)
    preview.value = url

    const res = await api.upload(file, 'sections/images')
    form.value.image = res.data.url
}

const uploadDoc = async (e) => {
    const file = e.target.files[0]
    if (!file) return

    const res = await api.upload(file, 'sections/docs')

    form.value.file_path = res.data.url
    form.value.file_name = file.name
}

const saveSection = async () => {
    const payload = {
        ...form.value,
        extra: JSON.parse(extraJson.value || '{}')
    }

    if (editingId.value)
        await api.put(`/admin/sections/${editingId.value}`, payload)
    else
        await api.post('/admin/sections', payload)

    await load()
    closeForm()
}

const remove = async (id) => {
    await api.delete(`/admin/sections/${id}`)
    await load()
}

const move = async (i, dir) => {
    const arr = [...sections.value]
    const j = i + dir

    if (j < 0 || j >= arr.length) return

    // swap (to‘g‘ri yozilishi)
    const temp = arr[i]
    arr[i] = arr[j]
    arr[j] = temp

    await api.post('/admin/sections/reorder', {
        items: arr.map((x, idx) => ({
            id: x.id,
            sort_order: idx
        }))
    })

    await load()
}

const getIcon = (type) => {
    switch (type) {
        case 'hero': return Eye
        case 'value': return Heart
        case 'team': return Users
        case 'legal': return Shield
        case 'doc': return FileText
        default: return FileText
    }
}

onMounted(load)
</script>
<style scoped>
.card {
    background: white;
    border-radius: 16px;
    padding: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 12px;
}

.input:focus {
    border-color: #2A7DE1;
    box-shadow: 0 0 0 2px rgba(42,125,225,0.2);
    outline: none;
}

.btn-primary {
    background: #2A7DE1;
    color: white;
    padding: 10px 14px;
    border-radius: 12px;
}

.btn-secondary {
    border: 1px solid #ddd;
    padding: 10px 14px;
    border-radius: 12px;
}

.upload-box {
    border: 2px dashed #ddd;
    padding: 18px;
    text-align: center;
    border-radius: 12px;
    cursor: pointer;
}

.preview-img {
    height: 120px;
    margin: auto;
    border-radius: 10px;
}

.icon-box {
    background: #f3f4f6;
    padding: 8px;
    border-radius: 10px;
}

.icon-btn {
    padding: 6px;
    border-radius: 8px;
}
.doc-upload {
    border: 2px dashed #cbd5e1;
    padding: 18px;
    border-radius: 14px;
    background: #f8fafc;
    cursor: pointer;
    transition: 0.2s;
}

.doc-upload:hover {
    border-color: #2A7DE1;
    background: #f0f7ff;
}
</style>
