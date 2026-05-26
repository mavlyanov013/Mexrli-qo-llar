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
                    <p><b>Sahifa manzili:</b> {{ current.slug }}</p>
                    <p><b>Meta sarlavhasi:</b> {{ current.meta_title }}</p>
                    <p><b>Meta tavsifi:</b> {{ current.meta_description }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded">
                    {{ current.content }}
                </div>

                <!-- SECTIONS -->
                <div v-if="current.sections?.length">
                    <h3 class="font-semibold mt-6 mb-3">Bo‘limlar</h3>

                    <div
                        v-for="section in current.sections"
                        :key="section.id"
                        class="border p-4 rounded-lg mt-3 bg-white space-y-3"
                    >
                        <!-- TYPE -->
                        <div class="text-xs text-gray-400 uppercase">
                            {{ section.type }}
                        </div>

                        <!-- TITLE -->
                        <input
                            v-model="section.title"
                            class="input"
                            :placeholder="t('admin.placeholders.pageTitle')"
                        />

                        <!-- SUBTITLE -->
                        <input
                            v-model="section.subtitle"
                            class="input"
                            :placeholder="t('admin.placeholders.pageSubtitle')"
                        />

                        <!-- CONTENT -->
                        <textarea
                            v-model="section.content"
                            class="input"
                            :placeholder="t('admin.placeholders.pageContent')"
                        />

                        <!-- EXTRA JSON -->
                        <textarea
                            v-model="section._extra"
                            class="input"
                            :placeholder="t('admin.placeholders.pageMeta')"
                        />

                        <!-- ACTIONS -->
                        <div class="flex gap-2 justify-end">
                            <button
                                class="btn-secondary"
                                @click="updateSection(section)"
                            >
                                Saqlash
                            </button>

                            <button
                                class="text-red-600"
                                @click="deleteSection(section.id)"
                            >
                                O‘chirish
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </template>

    </AdminCrudShell>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'

import AdminCrudShell from '@/admin/components/common/AdminCrudShell.vue'
import AdminTable from '@/admin/components/common/AdminTable.vue'
import ListState from '@/components/shared/ListState.vue'
import pageService from '@/services/pageService'

const { t } = useI18n()

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
    { key: 'title', label: 'Sarlavha' },
    { key: 'slug', label: 'Sahifa manzili' },
    { key: 'actions', label: 'Amallar' },
]

/* ================= API ================= */

const fetchPages = async () => {
    loading.value = true
    try {
        const res = await pageService.getAll()
        pages.value = res.data.data
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}
const updateSection = async (section) => {
    try {
        let extra = {}

        try {
            extra = JSON.parse(section._extra || '{}')
        } catch {
            extra = {}
        }

        await api.put(`/admin/sections/${section.id}`, {
            page_id: current.value.id,
            type: section.type,
            title: section.title,
            subtitle: section.subtitle,
            content: section.content,
            extra
        })

        await openPage(current.value.id)
    } catch (e) {
        console.error(e)
    }
}

const deleteSection = async (id) => {
    if (!confirm('O‘chirishni tasdiqlaysizmi?')) return

    await api.delete(`/admin/sections/${id}`)
    await openPage(current.value.id)
}

const openPage = async (id) => {
    selectedId.value = id

    const res = await pageService.getById(id)
    const data = res.data.data

    data.sections = data.sections.map(s => ({
        ...s,
        _extra: JSON.stringify(s.extra || {}, null, 2)
    }))

    current.value = data
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

<style scoped>
.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 8px 10px;
}
.btn-secondary {
    border: 1px solid #ddd;
    padding: 6px 12px;
    border-radius: 8px;
}
</style>
