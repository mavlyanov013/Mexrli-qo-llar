<template>
    <div class="admin-localized-fields space-y-5">
        <p class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs leading-relaxed text-blue-800">
            <strong>O‘zbek (lotin)</strong> majburiy.
            <strong>O‘zbek (kirill)</strong>ni qo‘lda yozishingiz mumkin — bo‘sh qoldirsangiz, lotindan avtomatik to‘ldiriladi.
            <span v-if="showRussian"> Rus tilini ixtiyoriy kiritishingiz mumkin.</span>
        </p>

        <div
            v-for="field in fields"
            :key="field.name"
            class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 space-y-3"
        >
            <p class="text-sm font-semibold text-gray-800">
                {{ field.label }}
            </p>

            <div
                class="grid grid-cols-1 gap-4"
                :class="showRussian ? 'md:grid-cols-3' : 'md:grid-cols-2'"
            >
                <div class="space-y-1.5">
                    <label class="field-sublabel">O‘zbek (lotin) *</label>

                    <textarea
                        v-if="field.type === 'textarea'"
                        :value="modelValue[`${field.name}_uz`]"
                        :rows="field.rows || 4"
                        :placeholder="field.placeholderUz || field.placeholder || ''"
                        class="field-input"
                        @input="updateUz(field.name, $event.target.value)"
                    />

                    <input
                        v-else
                        :value="modelValue[`${field.name}_uz`]"
                        :placeholder="field.placeholderUz || field.placeholder || ''"
                        class="field-input"
                        @input="updateUz(field.name, $event.target.value)"
                    />
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <label class="field-sublabel">O‘zbek (kirill)</label>
                        <button
                            type="button"
                            class="text-xs text-[#2A7DE1] hover:underline"
                            @click="fillOzFromUz(field.name)"
                        >
                            Lotindan to‘ldirish
                        </button>
                    </div>

                    <textarea
                        v-if="field.type === 'textarea'"
                        :value="modelValue[`${field.name}_oz`] || ''"
                        :rows="field.rows || 4"
                        :placeholder="field.placeholderOz || 'Kirill matn...'"
                        class="field-input"
                        @input="updateOz(field.name, $event.target.value)"
                    />

                    <input
                        v-else
                        :value="modelValue[`${field.name}_oz`] || ''"
                        :placeholder="field.placeholderOz || 'Kirill matn...'"
                        class="field-input"
                        @input="updateOz(field.name, $event.target.value)"
                    />
                </div>

                <div v-if="showRussian" class="space-y-1.5">
                    <label class="field-sublabel">Rus (ixtiyoriy)</label>

                    <textarea
                        v-if="field.type === 'textarea'"
                        :value="modelValue[`${field.name}_ru`] || ''"
                        :rows="field.rows || 4"
                        :placeholder="field.placeholderRu || 'Ruscha matn...'"
                        class="field-input"
                        @input="updateRu(field.name, $event.target.value)"
                    />

                    <input
                        v-else
                        :value="modelValue[`${field.name}_ru`] || ''"
                        :placeholder="field.placeholderRu || 'Ruscha matn...'"
                        class="field-input"
                        @input="updateRu(field.name, $event.target.value)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { transliterateLatinToCyrillic } from '@/utils/uzbekTransliterate'

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    fields: {
        type: Array,
        required: true,
    },
    showRussian: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['update:modelValue'])

const applyPatch = (patch) => {
    Object.assign(props.modelValue, patch)
    emit('update:modelValue', props.modelValue)
}

/** Kirill qo‘lda tahrirlangan — lotin o‘zgarganda avtomatik ustiga yozilmaydi */
const ozManual = ref({})

const isOzManual = (name) => Boolean(ozManual.value[name])

const syncOzManualFlags = () => {
    const next = {}

    props.fields.forEach((field) => {
        const name = field.name
        const uz = String(props.modelValue[`${name}_uz`] || '').trim()
        const oz = String(props.modelValue[`${name}_oz`] || '').trim()
        const auto = transliterateLatinToCyrillic(uz).trim()

        next[name] = Boolean(oz && oz !== auto)
    })

    ozManual.value = next
}

watch(
    () => props.fields.map((f) => [f.name, props.modelValue[`${f.name}_uz`], props.modelValue[`${f.name}_oz`]]),
    syncOzManualFlags,
    { immediate: true }
)

const updateUz = (name, value) => {
    const patch = {
        [`${name}_uz`]: value,
    }

    if (!isOzManual(name)) {
        patch[`${name}_oz`] = transliterateLatinToCyrillic(value)
    }

    applyPatch(patch)
}

const updateOz = (name, value) => {
    ozManual.value = {
        ...ozManual.value,
        [name]: true,
    }

    applyPatch({
        [`${name}_oz`]: value,
    })
}

const fillOzFromUz = (name) => {
    const uz = String(props.modelValue[`${name}_uz`] || '')

    ozManual.value = {
        ...ozManual.value,
        [name]: false,
    }

    applyPatch({
        [`${name}_oz`]: transliterateLatinToCyrillic(uz),
    })
}

const updateRu = (name, value) => {
    applyPatch({
        [`${name}_ru`]: value,
    })
}
</script>

<style scoped>
.field-sublabel {
    display: block;
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
}
.field-input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 14px;
    background: #fff;
}
.field-input:focus {
    outline: none;
    border-color: #2a7de1;
    box-shadow: 0 0 0 2px rgba(42, 125, 225, 0.15);
}
</style>
