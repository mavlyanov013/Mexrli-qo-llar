<template>
    <AdminCrudShell title="About Page Builder">

        <!-- CREATE SECTION -->
        <div class="bg-white p-5 rounded-xl shadow mb-6">

            <h2 class="font-bold mb-4">Add Section</h2>

            <select v-model="form.type" class="border p-2 rounded w-full mb-3">
                <option value="value">Value Card</option>
                <option value="doc">Document</option>
                <option value="team">Team Member</option>
            </select>

            <input v-model="form.title" placeholder="Title"
                   class="border p-2 rounded w-full mb-3" />

            <input v-model="form.subtitle" placeholder="Subtitle"
                   class="border p-2 rounded w-full mb-3" />

            <textarea v-model="form.content"
                      placeholder="Content"
                      class="border p-2 rounded w-full mb-3"></textarea>

            <!-- EXTRA JSON -->
            <textarea v-model="extraJson"
                      placeholder='{"icon":"Eye","tone":"blue"}'
                      class="border p-2 rounded w-full mb-3"></textarea>

            <button @click="createSection"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                Save
            </button>

        </div>

        <!-- LIST -->
        <div class="space-y-3">

            <div v-for="s in sections" :key="s.id"
                 class="bg-white p-4 rounded border">

                <div class="flex justify-between">
                    <div>
                        <b>{{ s.type }}</b> - {{ s.title }}
                        <p class="text-sm text-gray-500">{{ s.content }}</p>
                    </div>

                    <button @click="remove(s.id)"
                            class="text-red-500">
                        Delete
                    </button>
                </div>

            </div>

        </div>

    </AdminCrudShell>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import AdminCrudShell from "../components/common/AdminCrudShell.vue";

const sections = ref([])

const form = ref({
    page_id: 1,
    type: 'value',
    title: '',
    subtitle: '',
    content: '',
})

const extraJson = ref('{}')

const load = async () => {
    const res = await api.get('/admin/pages/1')
    sections.value = res.data.data.sections
}

const createSection = async () => {
    await api.post('/admin/sections', {
        ...form.value,
        extra: JSON.parse(extraJson.value || '{}')
    })

    await load()
}

const remove = async (id) => {
    await api.delete(`/admin/sections/${id}`)
    await load()
}

onMounted(load)
</script>
