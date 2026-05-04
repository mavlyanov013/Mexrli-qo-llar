<template>
    <div class="p-6 text-center text-sm text-gray-500">
        {{ message }}
    </div>
</template>

<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
})
</script>
