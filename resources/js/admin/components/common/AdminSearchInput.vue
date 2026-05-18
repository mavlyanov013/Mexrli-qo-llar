<script setup>
defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Search...',
    },
})

defineEmits(['update:modelValue'])
</script>

<template>
    <div class="w-full lg:w-80">
        <input
            :value="modelValue"
            type="text"
            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none"
            :placeholder="placeholder"
            @input="$emit('update:modelValue', $event.target.value)"
        />
    </div>
</template>
