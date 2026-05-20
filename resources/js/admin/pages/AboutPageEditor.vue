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

        <!-- ================= FORM ================= -->
        <div v-if="showForm" class="bg-white rounded-2xl shadow p-6 space-y-6">

            <!-- HEADER -->
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">
                    {{ editingId ? 'Bo‘limni tahrirlash' : 'Yangi bo‘lim yaratish' }}
                </h3>

                <button @click="closeForm" class="text-gray-400 hover:text-red-500">
                    ✕
                </button>
            </div>

            <!-- TYPE -->
            <div>
                <label class="text-sm text-gray-600">Bo‘lim turi</label>
                <select v-model="form.type" class="input mt-1">
                    <option value="hero">🏠 Biz haqimizda</option>
                    <option value="value">⭐ Bizning qadriyatlarimiz</option>
                    <option value="doc">📄 Hujjat</option>
                    <option value="team">👥 Jamoa</option>
                    <option value="legal">⚖️ Huquqiy</option>
                </select>
            </div>

            <!-- TITLE -->
            <div>
                <label class="text-sm text-gray-600">Sarlavha</label>
                <input
                    v-model="form.title"
                    class="input mt-1"
                    placeholder="Masalan: Bizning maqsadimiz..."
                />
            </div>

            <!-- SUBTITLE -->
            <div>
                <label class="text-sm text-gray-600">Subtitr</label>
                <input
                    v-model="form.subtitle"
                    class="input mt-1"
                    placeholder="Qisqa tushuntirish..."
                />
            </div>

            <!-- CONTENT -->
            <div>
                <label class="text-sm text-gray-600">Tarkib</label>
                <textarea
                    v-model="form.content"
                    class="input mt-1"
                    rows="4"
                    placeholder="Bo‘lim haqida batafsil yozing..."
                ></textarea>
            </div>

            <!-- IMAGE UPLOAD -->
            <div>
                <label class="text-sm text-gray-600">Rasm</label>

                <div class="upload-box mt-2" @click="triggerImage">
                    <input ref="imageInput" type="file" hidden @change="uploadImage" />

                    <div v-if="!preview" class="text-gray-400">
                        📷 Rasm yuklash uchun bosing
                    </div>

                    <img v-else :src="preview" class="rounded-lg max-h-40 mx-auto" />
                </div>
            </div>
            <!-- ACTIONS -->
            <div class="flex justify-end gap-3">
                <button class="btn-secondary" @click="closeForm">
                    Bekor qilish
                </button>

                <button class="btn-primary" @click="saveSection">
                    Saqlash
                </button>
            </div>

        </div>


        <!-- ================= LIST ================= -->
        <div v-else>
            <div class="grid gap-3">

                <div
                    v-for="(s, idx) in sections"
                    :key="s.id"
                    class="card flex justify-between"
                >

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
const imageInput = ref(null)

const load = async () => {
    const res = await api.get('/admin/pages')
    const about = res.data.data.find(p => p.slug === 'about')

    pageId.value = about.id
    form.value.page_id = about.id

    const page = await api.get(`/admin/pages/${about.id}`)
    sections.value = page.data.data.sections
}

const openCreate = () => {
    resetForm()
    showForm.value = true
}
const resetForm = () => {
    form.value = {
        page_id: pageId.value,
        type: 'hero',
        title: '',
        subtitle: '',
        content: '',
        image: '',
        file_path: '',
        file_name: ''
    }

    preview.value = ''
    extraJson.value = '{}'
    editingId.value = null
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
    const file = e.target.files?.[0]
    if (!file) return

    preview.value = URL.createObjectURL(file)

    const formData = new FormData()
    formData.append('file', file)
    formData.append('directory', 'about/sections')

    const res = await api.post('/admin/media', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })

    form.value.image = res.data.data.url
}
const triggerImage = () => {
    imageInput.value?.click()
}

const uploadDoc = async (e) => {
    const file = e.target.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append('file', file)
    formData.append('directory', 'sections/docs')

    try {
        const res = await api.post('/admin/media', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        form.value.file_path = res.data.data.url
        form.value.file_name = file.name
    } catch (err) {
        console.error(err)
        alert('Hujjat yuklashda xatolik')
    }
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
        case 'biz haqimizda': return Eye
        case 'Bizning qadriyatlarimiz': return Heart
        case 'jamoa': return Users
        case 'qonuniy': return Shield
        case 'doc-file': return FileText
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
.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 12px;
    transition: 0.2s;
}

.input:focus {
    border-color: #2A7DE1;
    box-shadow: 0 0 0 2px rgba(42,125,225,0.15);
    outline: none;
}
</style>
