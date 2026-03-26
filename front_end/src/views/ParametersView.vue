<template>
    <h1>Bienvenue {{ authStore.user?.name }}</h1>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
const authStore = useAuthStore()
</script>

<style scoped>

</style>