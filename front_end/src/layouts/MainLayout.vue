<template>
  <div class="app-layout">
    <NotificationToast />
    <header class="global-header">
      <div class="header-logo">
        <span class="header-title">Le shop du coin</span>
      </div>
      <div class="header-actions">
        <button class="header-theme-toggle" @click="themeStore.toggleTheme" title="Changer de thème">
          <i :class="themeStore.dark ? 'bx bx-sun' : 'bx bx-moon'"></i>
        </button>
        <template v-if="!authStore.isAuthenticated">
          <router-link to="/login" class="header-auth-btn header-login-btn">Connexion</router-link>
          <router-link to="/register" class="header-auth-btn header-register-btn">Inscription</router-link>
        </template>
      </div>
    </header>
    <div class="main-layout">
      <Sidebar v-if="authStore.isAuthenticated" />
      <main class="main-content">
        <router-view v-slot="{ Component, route }">
          <transition name="fade" mode="out-in">
            <component :is="Component" :key="route.path" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/components/Sidebar.vue';
import NotificationToast from '@/components/NotificationToast.vue';
import { onMounted } from 'vue';
import { useThemeStore } from '@/stores/theme';
import { useAuthStore } from '@/stores/auth';

const themeStore = useThemeStore();
const authStore = useAuthStore();

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
  font-family: 'Cookie', cursive;
  font-size: 2.5rem;
  letter-spacing: 1px;
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

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-auth-btn {
  padding: 0.5rem 1.25rem;
  border-radius: 8px;
  font-weight: 500;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.header-login-btn {
  color: var(--primary);
  background-color: transparent;
  border: 1px solid var(--primary);
}

.header-login-btn:hover {
  background-color: var(--primary);
  color: var(--background);
}

.header-register-btn {
  color: var(--background);
  background-color: var(--primary);
  border: 1px solid var(--primary);
}

.header-register-btn:hover {
  background-color: var(--secondary);
  border-color: var(--secondary);
  color: var(--background);
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
