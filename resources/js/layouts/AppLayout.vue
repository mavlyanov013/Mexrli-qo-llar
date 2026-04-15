<template>
    <div class="min-h-screen bg-gray-50">
        <Navbar v-if="!isAdminPage" />

        <main :class="isAdminPage ? '' : 'pt-20'">
            <slot />
        </main>

        <Footer v-if="!isAdminPage" />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'

const route = useRoute()

const isAdminPage = computed(() => route.path.startsWith('/admin'))
</script>
