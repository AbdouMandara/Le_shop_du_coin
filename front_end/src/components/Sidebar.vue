<template>
  <aside 
    class="sidebar" 
    :class="{ 'sidebar--collapsed': collapsed }"
  >

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
        <span v-if="item.id === 'cart' && cartTotalCount > 0 && !collapsed" class="cart-badge">
          {{ cartTotalCount }}
        </span>
      </router-link>
    </nav>

    <div class="sidebar__footer">
      <div v-if="authStore.isAuthenticated" class="sidebar__user" ref="userMenuRef">
         <div class="user-profile">
            <div class="user-avatar" :style="{ backgroundColor: 'var(--primary)', color: '#fff' }">
               {{ getInitials(authStore.user?.name) }}
            </div>
            <div class="user-info" v-if="!collapsed">
               <span class="user-name">{{ authStore.user?.name }}</span>
               <span class="user-email">{{ authStore.user?.email }}</span>
            </div>
            <button class="user-menu-btn" v-if="!collapsed" @click.stop="toggleUserMenu">
               <i class='bx bx-dots-horizontal-rounded'></i>
            </button>
            
            <div v-if="showUserMenu && !collapsed" class="user-modal">
               <button class="user-modal-item" @click="handleLogout">
                  <i class='bx bx-log-out'></i>
                  Déconnexion
               </button>
            </div>
         </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const cartStore = useCartStore();
const router = useRouter();

const collapsed = ref(false);

const showUserMenu = ref(false);
const userMenuRef = ref(null);

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
};

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const closeMenu = (e) => {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        showUserMenu.value = false;
    }
};

onMounted(() => document.addEventListener('click', closeMenu));
onUnmounted(() => document.removeEventListener('click', closeMenu));

const cartTotalCount = computed(() => {
    return cartStore.items.reduce((acc, item) => acc + item.quantity, 0);
});

const navItems = computed(() => {
    const items = [];

    if (authStore.isAuthenticated) {
        if (authStore.isUser) {
            items.push({ label: 'Produits', path: '/client/products', icon: 'bx bx-grid-alt' });
            items.push({ label: 'Favoris', path: '/client/favorites', icon: 'bx bx-heart' });
            items.push({ label: 'Commandes', path: '/client/orders', icon: 'bx bx-package' });
            items.push({ label: 'Panier', path: '/client/cart', icon: 'bx bx-cart', id: 'cart' });
        } else if (authStore.isAdmin) {
            items.push({ label: 'Tableau de bord', path: '/admin', icon: 'bx bx-shield-quarter' });
            items.push({ label: 'Produits', path: '/admin/products', icon: 'bx bx-grid-alt' });
            items.push({ label: 'Livreurs', path: '/admin/livreurs', icon: 'bx bx-cycling' });
            items.push({ label: 'Commandes', path: '/admin/orders', icon: 'bx bx-package' });
        } else if (authStore.isLivreur) {
            items.push({ label: 'Livraisons', path: '/livreur', icon: 'bx bx-cycling' });
            items.push({ label: 'Commandes', path: '/livreur/orders', icon: 'bx bx-package' });
        }
    } else {
        items.push({ label: 'Accueil', path: '/', icon: 'bx bx-home' });
        items.push({ label: 'Produits', path: '/products', icon: 'bx bx-grid-alt' });
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
  height: calc(100vh - 60px);
  background-color: var(--background);
  color: var(--text);
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: sticky;
  top: 60px;
  z-index: 100;
  border-right: 1px solid var(--border);
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
  font-size: 1.5rem;
  color: var(--primary);
}

.sidebar__toggle {
  background: none;
  border: none;
  color: var(--text);
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
  color: var(--text);
  opacity: 0.7;
  transition: all 0.2s ease;
  border-left: 4px solid transparent;
}

.sidebar__link:hover,
.sidebar__link.router-link-exact-active {
  color: var(--primary);
  opacity: 1;
  background-color: var(--neutral);
}

.sidebar__link i {
  font-size: 1.5rem;
  min-width: 24px;
}

.cart-badge {
  background-color: var(--secondary);
  color: #FFFFFF;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.1rem 0.6rem;
  border-radius: 12px;
  margin-left: auto;
}

.sidebar__footer {
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  border-top: 1px solid var(--border);
}

.sidebar__user {
  position: relative;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.user-profile:hover {
  background-color: var(--neutral);
}

.user-avatar {
  min-width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  flex: 1;
  overflow: hidden;
}

.user-name {
  font-weight: 600;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--text);
}

.user-email {
  font-size: 0.75rem;
  color: #888;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-menu-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text);
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
}

.user-menu-btn:hover {
  background-color: var(--neutral);
}

.user-modal {
  position: absolute;
  bottom: calc(100% + 10px);
  right: 0;
  left: 0;
  background-color: var(--surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  padding: 0.5rem 0;
  z-index: 10;
}

.user-modal-item {
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  padding: 0.75rem 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text);
  font-size: 0.9rem;
  transition: background-color 0.2s, color 0.2s;
}

.user-modal-item:hover {
  background-color: var(--neutral);
  color: var(--primary);
}
</style>
