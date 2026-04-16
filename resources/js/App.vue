<template>
    <AppLayout>
        <LiveActivityBar />
        <router-view />
    </AppLayout>
</template>

<script setup>
import AppLayout from './layouts/AppLayout.vue'
import LiveActivityBar from './components/home/LiveActivityBar.vue'
</script>
