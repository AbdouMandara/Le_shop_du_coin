<template>
  <div class="app-layout">
    <header class="global-header">
      <div class="header-logo">
        <span class="header-title">Le shop du coin</span>
      </div>
      <button class="header-theme-toggle" @click="themeStore.toggleTheme" title="Changer de thème">
        <i :class="themeStore.dark ? 'bx bx-sun' : 'bx bx-moon'"></i>
      </button>
    </header>
    <div class="main-layout">
      <Sidebar />
      <main class="main-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/components/Sidebar.vue';
import { onMounted } from 'vue';
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();

onMounted(() => {
  themeStore.applyTheme();
});
</script>

<style scoped>
.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.global-header {
  height: 60px;
  background-color: var(--background);
  color: var(--text);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  z-index: 1001;
  position: sticky;
  top: 0;
  border-bottom: 1px solid var(--border);
}

.header-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--primary);
}

.header-title {
  color: var(--text);
}

.header-theme-toggle {
  background: none;
  border: none;
  color: var(--text);
  font-size: 1.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  transition: color 0.2s;
}

.header-theme-toggle:hover {
  color: var(--primary);
}

.main-layout {
  display: flex;
  flex: 1;
}

.main-content {
  flex: 1;
  background-color: var(--background);
  transition: background-color 0.3s;
  overflow-y: auto;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
