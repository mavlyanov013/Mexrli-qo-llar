<template>
    <div class="space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Aloqa ma’lumotlari</h1>
                <p class="mt-1 text-sm text-gray-500 max-w-2xl">
                    Manzil, telefon, email va xarita
                    <a href="/contact" target="_blank" class="text-[#2A7DE1] hover:underline">/contact</a>
                    sahifasida ko‘rsatiladi.
                </p>
            </div>
            <a href="/contact" target="_blank" class="btn-secondary shrink-0">
                Saytni ko‘rish
            </a>
        </div>

        <form
            class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5 max-w-3xl"
            @submit.prevent="save"
        >
            <p
                v-if="message"
                class="rounded-lg px-4 py-3 text-sm"
                :class="messageType === 'error' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'"
            >
                {{ message }}
            </p>

            <div>
                <label class="label">Manzil</label>
                <textarea
                    v-model="form.address"
                    class="input"
                    rows="3"
                    placeholder="Masalan: Toshkent sh., ..."
                />
            </div>

            <div>
                <label class="label">Telefon raqam</label>
                <PhoneInput ref="phoneInputRef" v-model="form.phone" input-class="input" />
            </div>

            <div>
                <label class="label">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    class="input"
                    placeholder="info@mehrli.uz"
                />
            </div>

            <div>
                <label class="label">Xarita (Google Maps embed yoki iframe URL)</label>
                <textarea
                    v-model="form.map_embed_url"
                    class="input"
                    rows="4"
                    placeholder="https://www.google.com/maps/embed?... yoki &lt;iframe src=&quot;...&quot;&gt;"
                />
                <p class="hint">Google Maps dan “Embed a map” kodini yoki to‘g‘ridan-to‘g‘ri iframe src havolasini kiriting.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label">Kenglik (lat)</label>
                    <input
                        v-model="form.map_lat"
                        type="number"
                        step="any"
                        class="input"
                        placeholder="41.3111"
                    />
                </div>
                <div>
                    <label class="label">Uzunlik (lng)</label>
                    <input
                        v-model="form.map_lng"
                        type="number"
                        step="any"
                        class="input"
                        placeholder="69.2797"
                    />
                </div>
            </div>
            <p class="hint -mt-2">Embed URL bo‘sh bo‘lsa, lat/lng dan OpenStreetMap xaritasi yaratiladi.</p>

            <div class="pt-4 border-t border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Ijtimoiy tarmoqlar</h2>
                <p class="hint mb-4">Footer va boshqa joylarda ko‘rinadi. Bo‘sh qoldirilsa, ikonka chiqmaydi.</p>

                <div class="space-y-4">
                    <div>
                        <label class="label">Instagram URL</label>
                        <input
                            v-model="form.instagram_url"
                            type="url"
                            class="input"
                            placeholder="https://instagram.com/..."
                        />
                    </div>
                    <div>
                        <label class="label">YouTube URL</label>
                        <input
                            v-model="form.youtube_url"
                            type="url"
                            class="input"
                            placeholder="https://youtube.com/..."
                        />
                    </div>
                    <div>
                        <label class="label">Facebook URL</label>
                        <input
                            v-model="form.facebook_url"
                            type="url"
                            class="input"
                            placeholder="https://facebook.com/..."
                        />
                    </div>
                    <div>
                        <label class="label">Telegram URL</label>
                        <input
                            v-model="form.telegram_url"
                            type="url"
                            class="input"
                            placeholder="https://t.me/..."
                        />
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary" :disabled="saving">
                    {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import contactInfoService from '@/services/contactInfoService'
import PhoneInput from '@/components/shared/PhoneInput.vue'

const phoneInputRef = ref(null)
const saving = ref(false)
const message = ref('')
const messageType = ref('success')

const form = reactive({
    address: '',
    phone: '',
    email: '',
    map_embed_url: '',
    map_lat: '',
    map_lng: '',
    instagram_url: '',
    youtube_url: '',
    facebook_url: '',
    telegram_url: '',
})

const load = async () => {
    const data = await contactInfoService.getAdmin()

    form.address = data?.address || ''
    form.phone = data?.phone || ''
    form.email = data?.email || ''
    form.map_embed_url = data?.map_embed_url || ''
    form.map_lat = data?.map_lat ?? ''
    form.map_lng = data?.map_lng ?? ''
    form.instagram_url = data?.instagram_url || ''
    form.youtube_url = data?.youtube_url || ''
    form.facebook_url = data?.facebook_url || ''
    form.telegram_url = data?.telegram_url || ''
}

const save = async () => {
    if (form.phone && !phoneInputRef.value?.validate()) {
        messageType.value = 'error'
        message.value = phoneInputRef.value?.getError?.() || "Telefon raqam +998 formatida bo'lishi kerak"
        return
    }

    saving.value = true
    message.value = ''

    try {
        const response = await contactInfoService.update({
            address: form.address,
            phone: form.phone || null,
            email: form.email || null,
            map_embed_url: form.map_embed_url || null,
            map_lat: form.map_lat === '' ? null : Number(form.map_lat),
            map_lng: form.map_lng === '' ? null : Number(form.map_lng),
            instagram_url: form.instagram_url || null,
            youtube_url: form.youtube_url || null,
            facebook_url: form.facebook_url || null,
            telegram_url: form.telegram_url || null,
        })

        const data = response?.data ?? response
        form.address = data?.address || form.address
        form.phone = data?.phone || form.phone
        form.email = data?.email || form.email
        form.map_embed_url = data?.map_embed_url || form.map_embed_url
        form.map_lat = data?.map_lat ?? form.map_lat
        form.map_lng = data?.map_lng ?? form.map_lng
        form.instagram_url = data?.instagram_url || ''
        form.youtube_url = data?.youtube_url || ''
        form.facebook_url = data?.facebook_url || ''
        form.telegram_url = data?.telegram_url || ''

        messageType.value = 'success'
        message.value = response?.message || 'Aloqa ma’lumotlari saqlandi'
    } catch (error) {
        messageType.value = 'error'
        message.value = error?.response?.data?.message || 'Saqlashda xatolik yuz berdi'
    } finally {
        saving.value = false
    }
}

onMounted(load)
</script>

<style scoped>
.label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 6px;
    color: #374151;
}

.input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 14px;
}

.input:focus {
    outline: none;
    border-color: #2A7DE1;
    box-shadow: 0 0 0 2px rgba(42, 125, 225, 0.15);
}

.hint {
    margin-top: 6px;
    font-size: 12px;
    color: #6b7280;
}

.btn-primary {
    background: #2A7DE1;
    color: white;
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: 600;
}

.btn-primary:disabled {
    opacity: 0.6;
}

.btn-secondary {
    border: 1px solid #e5e7eb;
    padding: 8px 14px;
    border-radius: 12px;
    font-size: 14px;
    color: #374151;
}
</style>
