<template>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Biz haqimizda sahifasi</h1>
            <p class="mt-1 text-sm text-gray-500 max-w-3xl">
                Quyidagi bo‘limlarni tahrirlaysiz — ular saytdagi
                <a href="/about" target="_blank" class="text-[#2A7DE1] hover:underline">/about</a>
                sahifasida ko‘rinadi. Missiya, qadriyatlar va boshqa matnlar statik (o‘zgarmaydi).
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-5">
            <!-- Navigation -->
            <nav class="bg-white rounded-2xl border border-gray-200 p-2 h-fit lg:sticky lg:top-24">
                <button
                    v-for="section in sections"
                    :key="section.id"
                    type="button"
                    class="nav-item w-full"
                    :class="{ 'nav-item-active': activeSection === section.id }"
                    @click="activeSection = section.id"
                >
                    <component :is="section.icon" class="w-4 h-4 shrink-0" />
                    <span class="flex-1 text-left">
                        <span class="block font-medium leading-tight">{{ section.label }}</span>
                        <span class="block text-xs opacity-70 mt-0.5">{{ section.hint }}</span>
                    </span>
                    <span
                        class="status-dot"
                        :class="sectionStatus[section.id] ? 'status-dot-ok' : 'status-dot-empty'"
                        :title="sectionStatus[section.id] ? 'Ma’lumot bor' : 'To‘ldirilmagan'"
                    />
                </button>
            </nav>

            <!-- Active panel -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 min-h-[420px]">
                <div class="flex items-start justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#2A7DE1]">
                            {{ currentSection.step }}
                        </p>
                        <h2 class="text-xl font-semibold text-gray-900 mt-1">
                            {{ currentSection.label }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">{{ currentSection.description }}</p>
                    </div>
                    <a href="/about" target="_blank" class="btn-secondary shrink-0">
                        Saytni ko‘rish
                    </a>
                </div>

                <p
                    v-if="sectionMessage"
                    class="mb-5 rounded-lg px-4 py-3 text-sm"
                    :class="sectionMessageType === 'error' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'"
                >
                    {{ sectionMessage }}
                </p>

                <!-- BANK -->
                <div v-show="activeSection === 'bank'" class="space-y-4">
                    <LocalizedFieldTabs
                        v-model="bankForm"
                        :fields="[{ name: 'bank', label: 'Bank nomi', type: 'input' }]"
                    />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Hisob raqami (UZS)</label>
                            <input v-model="bankForm.account_uzs" class="input font-mono" placeholder="2020..." />
                        </div>
                        <div>
                            <label class="label">MFO / BIK</label>
                            <input v-model="bankForm.mfo_bik" class="input font-mono" placeholder="00014" />
                        </div>
                    </div>
                    <div class="pt-2">
                        <button type="button" class="btn-primary" :disabled="savingBank" @click="saveBank">
                            {{ savingBank ? 'Saqlanmoqda...' : 'Saqlash' }}
                        </button>
                    </div>
                </div>

                <!-- LEGAL -->
                <div v-show="activeSection === 'legal'" class="space-y-4">
                    <LocalizedFieldTabs
                        v-model="legalForm"
                        :fields="[
                            { name: 'org_name', label: 'Tashkilot nomi', type: 'input' },
                            { name: 'legal_address', label: 'Yuridik manzil', type: 'textarea', rows: 3 },
                        ]"
                    />
                    <div>
                        <label class="label">STIR</label>
                        <input v-model="legalForm.inn" class="input font-mono" />
                    </div>
                    <div class="pt-2">
                        <button type="button" class="btn-primary" :disabled="savingLegal" @click="saveLegal">
                            {{ savingLegal ? 'Saqlanmoqda...' : 'Saqlash' }}
                        </button>
                    </div>
                </div>

                <!-- DOCUMENTS -->
                <div v-show="activeSection === 'docs'" class="space-y-5">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="doc in documentForms"
                            :key="doc.key"
                            type="button"
                            class="doc-tab"
                            :class="{ 'doc-tab-active': activeDocKey === doc.key }"
                            @click="activeDocKey = doc.key"
                        >
                            {{ doc.shortLabel }}
                            <span
                                class="status-dot ml-1"
                                :class="doc.form.file_url ? 'status-dot-ok' : 'status-dot-empty'"
                            />
                        </button>
                    </div>

                    <div v-if="activeDocument" class="space-y-4 rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                        <h3 class="font-semibold text-gray-900">{{ activeDocument.label }}</h3>

                        <LocalizedFieldTabs
                            v-model="activeDocument.form"
                            :fields="[
                                { name: 'title', label: 'Sarlavha', type: 'input' },
                                { name: 'description', label: 'Qisqa tavsif', type: 'textarea', rows: 2 },
                            ]"
                        />
                        <div>
                            <label class="label">Fayl (PDF yoki rasm)</label>
                            <input
                                type="file"
                                accept=".pdf,image/*"
                                class="input"
                                @change="onDocumentFileChange(activeDocument.key, $event)"
                            />
                            <a
                                v-if="activeDocument.form.file_url"
                                :href="activeDocument.form.file_url"
                                target="_blank"
                                class="mt-2 inline-flex items-center gap-1 text-sm text-[#2A7DE1]"
                            >
                                Joriy faylni ochish
                            </a>
                            <p v-else class="mt-1 text-xs text-amber-600">
                                Fayl yuklanmagan — saytda hujjat bosilmaydi.
                            </p>
                        </div>
                        <div class="pt-1">
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="activeDocument.saving"
                                @click="saveDocument(activeDocument.key)"
                            >
                                {{ activeDocument.saving ? 'Saqlanmoqda...' : 'Saqlash' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TEAM -->
                <div v-show="activeSection === 'team'" class="space-y-5">
                    <div class="rounded-xl border border-dashed border-gray-200 p-4 bg-gray-50/50">
                        <p class="text-sm font-medium text-gray-800 mb-3">
                            {{ editingTeamId ? 'A’zoni tahrirlash' : 'Yangi jamoa a’zosi' }}
                        </p>
                        <form class="space-y-4" @submit.prevent="saveTeamMember">
                            <LocalizedFieldTabs
                                v-model="teamForm"
                                :fields="[
                                    { name: 'name', label: 'Ism', type: 'input' },
                                    { name: 'position', label: 'Lavozim', type: 'input' },
                                ]"
                            />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Tartib raqami</label>
                                <input v-model.number="teamForm.sort_order" type="number" min="0" class="input" />
                            </div>
                            <div>
                                <label class="label">Rasm</label>
                                <input type="file" accept="image/*" class="input" @change="onTeamPhotoChange" />
                                <img
                                    v-if="teamPhotoPreview"
                                    :src="teamPhotoPreview"
                                    alt=""
                                    class="mt-2 h-16 w-16 rounded-full object-cover border"
                                />
                            </div>
                            </div>
                            <div class="md:col-span-2 flex gap-2">
                                <button type="submit" class="btn-primary" :disabled="savingTeam">
                                    {{ savingTeam ? 'Saqlanmoqda...' : (editingTeamId ? 'Yangilash' : 'Qo‘shish') }}
                                </button>
                                <button v-if="editingTeamId" type="button" class="btn-secondary" @click="resetTeamForm">
                                    Bekor qilish
                                </button>
                            </div>
                        </form>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-3">
                            Jamoa ro‘yxati ({{ teamMembers.length }})
                        </p>
                        <p v-if="!teamMembers.length" class="text-sm text-gray-400 py-6 text-center border rounded-xl">
                            Hali a’zo qo‘shilmagan
                        </p>
                        <div v-else class="grid gap-2">
                            <div
                                v-for="member in teamMembers"
                                :key="member.id"
                                class="flex items-center justify-between rounded-xl border p-3"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <img
                                        v-if="member.photo_url"
                                        :src="member.photo_url"
                                        :alt="member.name"
                                        class="h-10 w-10 rounded-full object-cover shrink-0"
                                    />
                                    <div
                                        v-else
                                        class="h-10 w-10 rounded-full bg-[#2A7DE1] text-white flex items-center justify-center text-xs font-bold shrink-0"
                                    >
                                        {{ initials(member.name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ member.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ member.position || '—' }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button type="button" class="btn-secondary text-xs" @click="editTeamMember(member)">
                                        Tahrirlash
                                    </button>
                                    <button type="button" class="text-red-600 text-xs px-2" @click="removeTeamMember(member.id)">
                                        O‘chirish
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { Building2, FileText, Landmark, Users } from 'lucide-vue-next'
import aboutService from '@/services/aboutService'
import LocalizedFieldTabs from '@/admin/components/common/LocalizedFieldTabs.vue'
import { emptyLocalizedFields, assignLocalizedFromRow, validateAdminLocalizedFields, buildAdminPayload } from '@/utils/localizedContent'

const activeSection = ref('bank')
const activeDocKey = ref('registration_certificate')
const sectionMessage = ref('')
const sectionMessageType = ref('success')

const savingBank = ref(false)
const savingLegal = ref(false)
const savingTeam = ref(false)

const bankForm = reactive({ ...emptyLocalizedFields(['bank']), account_uzs: '', mfo_bik: '' })
const legalForm = reactive({ ...emptyLocalizedFields(['org_name', 'legal_address']), inn: '' })

const documentMeta = {
    registration_certificate: {
        label: 'Davlat ro‘yxatidan o‘tganlik guvohnomasi',
        shortLabel: 'Ro‘yxat guvohnomasi',
    },
    organization_charter: {
        label: 'Tashkilot nizomi',
        shortLabel: 'Nizom',
    },
}

const documentForms = ref([])
const documentFiles = ref({})

const teamMembers = ref([])
const editingTeamId = ref(null)
const teamPhotoPreview = ref('')
const teamPhotoFile = ref(null)
const teamForm = reactive({ ...emptyLocalizedFields(['name', 'position']), sort_order: 0, photo: '' })

const sections = [
    {
        id: 'bank',
        step: '1 / 4',
        label: 'Bank rekvizitlari',
        hint: 'Hisob, MFO',
        icon: Landmark,
        description: 'Xayriya uchun bank ma’lumotlari — saytda alohida blokda chiqadi.',
    },
    {
        id: 'legal',
        step: '2 / 4',
        label: 'Yuridik ma’lumotlar',
        hint: 'STIR, manzil',
        icon: Building2,
        description: 'Tashkilot nomi, STIR va yuridik manzil.',
    },
    {
        id: 'docs',
        step: '3 / 4',
        label: 'Hujjatlar',
        hint: '2 ta fayl',
        icon: FileText,
        description: 'Yuklab olinadigan hujjatlar — guvohnoma va nizom.',
    },
    {
        id: 'team',
        step: '4 / 4',
        label: 'Bizning jamoa',
        hint: 'A’zolar',
        icon: Users,
        description: 'Jamoa a’zolari: ism, lavozim va rasm.',
    },
]

const currentSection = computed(() => {
    return sections.find((s) => s.id === activeSection.value) ?? sections[0]
})

const activeDocument = computed(() => {
    return documentForms.value.find((doc) => doc.key === activeDocKey.value) ?? null
})

const sectionStatus = computed(() => {
    const docsFilled = documentForms.value.filter((d) => d.form.file_url).length

    return {
        bank: Boolean(bankForm.bank_uz || bankForm.account_uzs || bankForm.mfo_bik),
        legal: Boolean(legalForm.org_name_uz || legalForm.inn || legalForm.legal_address_uz),
        docs: docsFilled > 0,
        team: teamMembers.value.length > 0,
    }
})

const showMessage = (text, type = 'success') => {
    sectionMessage.value = text
    sectionMessageType.value = type
}

const clearMessage = () => {
    sectionMessage.value = ''
}

const initials = (name) => {
    return String(name || '')
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase() || 'T'
}

const load = async () => {
    const res = await aboutService.getAdminContent()
    const data = res.data ?? res

    Object.assign(bankForm, data.bank ?? {})
    Object.assign(legalForm, data.legal ?? {})
    teamMembers.value = data.team ?? []

    documentForms.value = (data.docs ?? []).map((doc) => ({
        key: doc.key,
        label: documentMeta[doc.key]?.label || doc.title,
        shortLabel: documentMeta[doc.key]?.shortLabel || doc.title,
        saving: false,
        form: (() => {
            const entry = {
                ...emptyLocalizedFields(['title', 'description']),
                file: doc.file || '',
                file_url: doc.file_url || '',
            }
            assignLocalizedFromRow(entry, doc, ['title', 'description'])
            return entry
        })(),
    }))

    if (!documentForms.value.find((d) => d.key === activeDocKey.value)) {
        activeDocKey.value = documentForms.value[0]?.key || 'registration_certificate'
    }
}

const onDocumentFileChange = (key, event) => {
    documentFiles.value[key] = event.target.files?.[0] || null
}

const saveDocument = async (key) => {
    const docEntry = documentForms.value.find((item) => item.key === key)
    if (!docEntry) return

    docEntry.saving = true
    clearMessage()

    try {
        const payload = buildAdminPayload(docEntry.form, ['title', 'description'])
        delete payload.file_url

        const pendingFile = documentFiles.value[key]
        if (pendingFile) {
            const uploaded = await aboutService.uploadDocumentFile(pendingFile)
            payload.file = uploaded.path || uploaded.url
            docEntry.form.file_url = uploaded.url
            documentFiles.value[key] = null
        } else if (docEntry.form.file) {
            payload.file = docEntry.form.file
        }

        const res = await aboutService.saveDocument(key, payload)
        showMessage(res.message || 'Hujjat saqlandi')

        if (res.data) {
            docEntry.form.title = res.data.title
            docEntry.form.description = res.data.description
            docEntry.form.file = res.data.file
            docEntry.form.file_url = res.data.file_url
        }
    } catch (error) {
        sectionMessage.value = error?.message || 'Hujjat saqlanmadi'
        sectionMessageType.value = 'error'
    } finally {
        docEntry.saving = false
    }
}

const saveBank = async () => {
    const fields = ['bank']
    const missing = validateAdminLocalizedFields(bankForm, fields)
    if (missing.length) {
        sectionMessage.value = `To‘ldiring: ${missing.join(', ')}`
        sectionMessageType.value = 'error'
        return
    }

    const bankPayload = buildAdminPayload(bankForm, fields)

    savingBank.value = true
    clearMessage()
    try {
        const res = await aboutService.saveBank(bankPayload)
        showMessage(res.message || 'Bank rekvizitlari saqlandi')
    } finally {
        savingBank.value = false
    }
}

const saveLegal = async () => {
    const fields = ['org_name', 'legal_address']
    const missing = validateAdminLocalizedFields(legalForm, fields)
    if (missing.length) {
        sectionMessage.value = `To‘ldiring: ${missing.join(', ')}`
        sectionMessageType.value = 'error'
        return
    }

    const legalPayload = buildAdminPayload(legalForm, fields)

    savingLegal.value = true
    clearMessage()
    try {
        const res = await aboutService.saveLegal(legalPayload)
        showMessage(res.message || 'Yuridik ma’lumotlar saqlandi')
    } finally {
        savingLegal.value = false
    }
}

const onTeamPhotoChange = (event) => {
    const file = event.target.files?.[0]
    teamPhotoFile.value = file || null
    teamPhotoPreview.value = file ? URL.createObjectURL(file) : ''
}

const resetTeamForm = () => {
    editingTeamId.value = null
    teamPhotoFile.value = null
    teamPhotoPreview.value = ''
    Object.assign(teamForm, { ...emptyLocalizedFields(['name', 'position']), sort_order: 0, photo: '' })
}

const editTeamMember = (member) => {
    editingTeamId.value = member.id
    assignLocalizedFromRow(teamForm, member, ['name', 'position'])
    Object.assign(teamForm, {
        sort_order: member.sort_order || 0,
        photo: member.photo || '',
    })
    teamPhotoPreview.value = member.photo_url || ''
    teamPhotoFile.value = null
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

const saveTeamMember = async () => {
    const fields = ['name', 'position']
    const missing = validateAdminLocalizedFields(teamForm, fields)
    if (missing.length) {
        sectionMessage.value = `To‘ldiring: ${missing.join(', ')}`
        sectionMessageType.value = 'error'
        return
    }

    savingTeam.value = true
    clearMessage()

    try {
        const payload = buildAdminPayload(teamForm, fields, {
            sort_order: teamForm.sort_order,
        })

        if (teamPhotoFile.value) {
            payload.photo = await aboutService.uploadTeamPhoto(teamPhotoFile.value)
        } else if (teamForm.photo) {
            payload.photo = teamForm.photo
        }

        if (editingTeamId.value) {
            const res = await aboutService.updateTeamMember(editingTeamId.value, payload)
            showMessage(res.message || 'Jamoa a’zosi yangilandi')
        } else {
            const res = await aboutService.createTeamMember(payload)
            showMessage(res.message || 'Jamoa a’zosi qo‘shildi')
        }

        await load()
        resetTeamForm()
    } catch (error) {
        sectionMessage.value = error?.message || 'Jamoa a’zosi saqlanmadi'
        sectionMessageType.value = 'error'
    } finally {
        savingTeam.value = false
    }
}

const removeTeamMember = async (id) => {
    if (!confirm('Jamoa a’zosini o‘chirishni tasdiqlaysizmi?')) return

    const res = await aboutService.removeTeamMember(id)
    showMessage(res.message || 'Jamoa a’zosi o‘chirildi')
    await load()
}

watch(activeSection, clearMessage)
watch(activeDocKey, clearMessage)

onMounted(load)
</script>

<style scoped>
.label {
    display: block;
    font-size: 0.875rem;
    color: #4b5563;
    margin-bottom: 0.25rem;
}
.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 14px;
    background: white;
}
.btn-primary {
    background: #2a7de1;
    color: white;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
}
.btn-primary:disabled {
    opacity: 0.6;
}
.btn-secondary {
    border: 1px solid #e5e7eb;
    padding: 8px 14px;
    border-radius: 12px;
    font-size: 13px;
    color: #374151;
    background: white;
}
.nav-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px;
    border-radius: 12px;
    color: #4b5563;
    transition: background 0.15s, color 0.15s;
}
.nav-item:hover {
    background: #f3f4f6;
    color: #111827;
}
.nav-item-active {
    background: #eff6ff;
    color: #1d4ed8;
}
.doc-tab {
    padding: 8px 14px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    font-size: 13px;
    color: #4b5563;
    display: inline-flex;
    align-items: center;
}
.doc-tab-active {
    border-color: #2a7de1;
    background: #eff6ff;
    color: #1d4ed8;
    font-weight: 500;
}
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    flex-shrink: 0;
    margin-top: 6px;
}
.status-dot-ok {
    background: #22c55e;
}
.status-dot-empty {
    background: #d1d5db;
}
</style>
