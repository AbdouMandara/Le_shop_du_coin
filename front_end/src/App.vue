<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue';
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();

onMounted(() => {
  themeStore.applyTheme();
});
</script>

<style>
/* Global styles already in main.css */
</style>
