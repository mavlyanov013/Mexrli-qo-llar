<template>
    <AdminCrudShell :title="title" :create-to="''">

        <!-- ================= LIST ================= -->
        <template v-if="!selectedId">
            <ListState :loading="loading" :error="error" :empty="pages.length === 0">

                <AdminTable :columns="columns" :rows="pages">

                    <template #cell-actions="{ row }">
                        <div class="flex gap-2">

                            <!-- VIEW -->
                            <button
                                @click="openPage(row.id)"
                                class="p-2 text-blue-600"
                            >
                                Ko‘rish
                            </button>

                            <!-- DELETE -->
                            <button
                                @click="remove(row.id)"
                                class="p-2 text-red-600"
                            >
                                O‘chirish
                            </button>

                        </div>
                    </template>

                </AdminTable>

            </ListState>
        </template>

        <!-- ================= VIEW ================= -->
        <template v-else-if="current">
            <div class="bg-white p-5 rounded-xl shadow space-y-4">

                <button
                    @click="backToList"
                    class="text-sm text-gray-500"
                >
                    ← Orqaga
                </button>

                <div class="space-y-2">
                    <p><b>Sarlavha:</b> {{ current.title }}</p>
                    <p><b>Slug:</b> {{ current.slug }}</p>
                    <p><b>Meta title:</b> {{ current.meta_title }}</p>
                    <p><b>Meta description:</b> {{ current.meta_description }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded">
                    {{ current.content }}
                </div>

                <!-- SECTIONS -->
                <div v-if="current.sections?.length">
                    <h3 class="font-semibold mt-4">Bo‘limlar</h3>

                    <div
                        v-for="section in current.sections"
                        :key="section.id"
                        class="border p-3 rounded mt-2"
                    >
                        <p><b>{{ section.title }}</b></p>
                        <p class="text-sm text-gray-600">
                            {{ section.content }}
                        </p>
                    </div>
                </div>

            </div>
        </template>

    </AdminCrudShell>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import ListState from '@/components/shared/ListState.vue'
import pageService from '@/services/pageService'

/* ================= STATE ================= */
const pages = ref([])
const loading = ref(false)
const error = ref(null)

const selectedId = ref(null)
const current = ref(null)

/* ================= TITLE ================= */
const title = computed(() =>
    selectedId.value ? 'Sahifa tafsiloti' : 'Sahifalar'
)

/* ================= TABLE ================= */
const columns = [
    { key: 'title', label: 'Title' },
    { key: 'slug', label: 'Slug' },
    { key: 'actions', label: 'Amallar' },
]

/* ================= API ================= */

const fetchPages = async () => {
    loading.value = true
    try {
        const res = await pageService.getAll()
        pages.value = res.data
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}

const openPage = async (id) => {
    selectedId.value = id

    try {
        const res = await pageService.getById(id)
        current.value = res.data
    } catch (e) {
        console.error(e)
    }
}

const backToList = () => {
    selectedId.value = null
    current.value = null
}

const remove = async (id) => {
    if (!confirm('O‘chirilsinmi?')) return

    await pageService.remove(id)
    await fetchPages()
}

/* ================= INIT ================= */
onMounted(fetchPages)
</script>
