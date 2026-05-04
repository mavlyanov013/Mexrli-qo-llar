<template>
    <a
        :href="href"
        target="_blank"
        rel="noopener noreferrer"
        :class="classes"
    >
        <slot />
    </a>
</template>

<script setup>
defineProps({
    href: { type: String, required: true },
    classes: {
        type: String,
        default: 'inline-flex items-center gap-1 text-sm text-[#2A7DE1] hover:underline',
    },
})
</script>
