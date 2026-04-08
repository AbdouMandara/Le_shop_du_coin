<template>
  <NewsletterModal />
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue';
import { useThemeStore } from '@/stores/theme';
import NewsletterModal from '@/components/NewsletterModal.vue';

const themeStore = useThemeStore();

onMounted(() => {
  themeStore.applyTheme();
});
</script>

<style>
/* Global styles already in main.css */
</style>
