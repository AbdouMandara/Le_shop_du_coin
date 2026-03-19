<template>
  <aside 
    class="sidebar" 
    :class="{ 'sidebar--collapsed': collapsed }"
  >
    <div class="sidebar__header">
      <div class="sidebar__logo" v-if="!collapsed">
        <i class='bx bxs-shopping-bag'></i>
        <span>E-Shop</span>
      </div>
      <button class="sidebar__toggle" @click="collapsed = !collapsed">
        <i :class="collapsed ? 'bx bx-chevron-right' : 'bx bx-chevron-left'"></i>
      </button>
    </div>

    <nav class="sidebar__nav">
      <router-link 
        v-for="item in navItems" 
        :key="item.path" 
        :to="item.path"
        class="sidebar__link"
        :title="item.label"
      >
        <i :class="item.icon"></i>
        <span v-if="!collapsed">{{ item.label }}</span>
      </router-link>
    </nav>

    <div class="sidebar__footer">
      <button class="sidebar__theme-toggle" @click="themeStore.toggleTheme">
        <i :class="themeStore.dark ? 'bx bx-sun' : 'bx bx-moon'"></i>
        <span v-if="!collapsed">{{ themeStore.dark ? 'Mode Clair' : 'Mode Sombre' }}</span>
      </button>
      
      <div v-if="authStore.isAuthenticated" class="sidebar__user">
          <button @click="handleLogout" class="sidebar__logout">
              <i class='bx bx-log-out'></i>
              <span v-if="!collapsed">Déconnexion</span>
          </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useThemeStore } from '@/stores/theme';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const themeStore = useThemeStore();
const authStore = useAuthStore();
const router = useRouter();

const collapsed = ref(false);

const navItems = computed(() => {
    const items = [];

    if (authStore.isAuthenticated) {
        if (authStore.isUser) {
            items.push({ label: 'Accueil', path: '/', icon: 'bx bx-home' });
            items.push({ label: 'Produits', path: '/products', icon: 'bx bx-grid-alt' });
            items.push({ label: 'Favoris', path: '/client/favorites', icon: 'bx bx-heart' });
            items.push({ label: 'Panier', path: '/client/cart', icon: 'bx bx-cart' });
            items.push({ label: 'Profil', path: '/client/profile', icon: 'bx bx-user' });
        } else if (authStore.isAdmin) {
            items.push({ label: 'Tableau de bord', path: '/admin', icon: 'bx bx-shield-quarter' });
            items.push({ label: 'Produits', path: '/products', icon: 'bx bx-grid-alt' });
            items.push({ label: 'Profil', path: '/admin/profile', icon: 'bx bx-user' });
        } else if (authStore.isLivreur) {
            items.push({ label: 'Livraisons', path: '/livreur', icon: 'bx bx-cycling' });
            items.push({ label: 'Profil', path: '/livreur/profile', icon: 'bx bx-user' });
        }
    } else {
        items.push({ label: 'Accueil', path: '/', icon: 'bx bx-home' });
        items.push({ label: 'Produits', path: '/products', icon: 'bx bx-grid-alt' });
        items.push({ label: 'Connexion', path: '/login', icon: 'bx bx-log-in' });
    }

    return items;
});

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};
</script>

<style scoped>
.sidebar {
  width: 260px;
  height: 100vh;
  background-color: var(--primary);
  color: #FFFFFF;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: sticky;
  top: 0;
  z-index: 100;
}

.sidebar--collapsed {
  width: 80px;
}

.sidebar__header {
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
}

.sidebar__logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--secondary);
}

.sidebar__toggle {
  background: none;
  border: none;
  color: #FFFFFF;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
}

.sidebar__nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem 0;
}

.sidebar__link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1.75rem;
  color: rgba(255, 255, 255, 0.7);
  transition: all 0.2s ease;
  border-left: 4px solid transparent;
}

.sidebar__link:hover,
.sidebar__link.router-link-active {
  color: #FFFFFF;
  background-color: rgba(255, 255, 255, 0.1);
  border-left-color: var(--secondary);
}

.sidebar__link i {
  font-size: 1.5rem;
  min-width: 24px;
}

.sidebar__footer {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar__theme-toggle,
.sidebar__logout {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  font-size: 1rem;
  padding: 0.5rem 0.25rem;
  width: 100%;
  transition: color 0.2s;
}

.sidebar__theme-toggle:hover,
.sidebar__logout:hover {
  color: #FFFFFF;
}

.sidebar__theme-toggle i,
.sidebar__logout i {
  font-size: 1.5rem;
}
</style>
