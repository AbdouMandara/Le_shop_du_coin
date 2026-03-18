<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <RouterView />
</template>

<style>
/* Global styles reset or basic setup */
:root {
  --primary: #00f2fe;
  --secondary: #4facfe;
  --bg-dark: #0f172a;
  --text-light: #f8fafc;
}

body {
  margin: 0;
  padding: 0;
  background-color: var(--bg-dark);
  color: var(--text-light);
  font-family: 'Inter', sans-serif;
  overflow-x: hidden;
}

* {
  box-sizing: border-box;
}
</style>
