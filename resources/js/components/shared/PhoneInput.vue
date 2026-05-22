<template>
    <div>
        <input
            :value="displayValue"
            type="tel"
            :placeholder="placeholder"
            :required="required"
            :class="inputClass"
            inputmode="tel"
            autocomplete="tel"
            @input="onInput"
            @blur="onBlur"
        />
        <p v-if="errorMessage" class="text-sm text-red-600 mt-1">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import {
    formatUzbekPhoneDisplay,
    normalizeUzbekPhone,
    validateUzbekPhone,
    UZ_PHONE_PLACEHOLDER,
} from '@/utils/uzbekPhone'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: UZ_PHONE_PLACEHOLDER,
    },
    inputClass: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:modelValue'])

const touched = ref(false)
const displayValue = ref(formatUzbekPhoneDisplay(props.modelValue))

watch(
    () => props.modelValue,
    (value) => {
        displayValue.value = formatUzbekPhoneDisplay(value)
    }
)

const errorMessage = computed(() => {
    if (!touched.value && !props.modelValue) {
        return null
    }

    return validateUzbekPhone(props.modelValue, { required: props.required })
})

const onInput = (event) => {
    const formatted = formatUzbekPhoneDisplay(event.target.value)
    displayValue.value = formatted
    emit('update:modelValue', normalizeUzbekPhone(formatted))
    touched.value = true
}

const onBlur = () => {
    touched.value = true
}

defineExpose({
    validate: () => {
        touched.value = true
        return !errorMessage.value
    },
    getError: () => errorMessage.value,
})
</script>
