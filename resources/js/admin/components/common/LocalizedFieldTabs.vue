<template>
    <div class="admin-localized-fields space-y-5">
        <p class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs leading-relaxed text-blue-800">
            <strong>O‘zbek (lotin)</strong> majburiy — kirill (ЎЗ) avtomatik shakllanadi.
            <span v-if="showRussian"> Rus tilini ixtiyoriy kiritishingiz mumkin — saytda UZ, ЎЗ va RU ko‘rinadi.</span>
        </p>

        <div
            v-for="field in fields"
            :key="field.name"
            class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 space-y-3"
        >
            <p class="text-sm font-semibold text-gray-800">
                {{ field.label }}
            </p>

            <div :class="showRussian ? 'grid grid-cols-1 md:grid-cols-2 gap-4' : ''">
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

                    <p
                        v-if="showCyrillicPreview && modelValue[`${field.name}_oz`]"
                        class="text-xs text-gray-500 leading-relaxed"
                    >
                        <span class="font-medium text-gray-600">ЎЗ (avtomatik):</span>
                        {{ modelValue[`${field.name}_oz`] }}
                    </p>
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
    showCyrillicPreview: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['update:modelValue'])

const updateUz = (name, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [`${name}_uz`]: value,
        [`${name}_oz`]: transliterateLatinToCyrillic(value),
    })
}

const updateRu = (name, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
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
