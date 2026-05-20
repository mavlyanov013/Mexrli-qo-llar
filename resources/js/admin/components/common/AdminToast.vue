<template>
    <div
        v-if="show"
        :class="[
            'fixed right-4 top-4 z-[100] rounded-xl px-4 py-3 text-sm shadow-lg',
            tone === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white'
        ]"
    >
        {{ message }}
    </div>
</template>

<script setup>
defineProps({
    show: { type: Boolean, default: false },
    message: { type: String, default: '' },
    tone: { type: String, default: 'muvaffaqiyat' },
})
</script>
