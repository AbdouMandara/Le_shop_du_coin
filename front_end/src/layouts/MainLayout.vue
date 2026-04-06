<template>
  <div class="app-layout">
    <NotificationToast />
    <header class="global-header">
      <div class="header-logo">
        <img src="/logo ctec sarl.jpg" alt="logo de l'entreprise">
      </div>
      <!-- Search Bar Centrale -->
      <div v-if="!authStore.isAuthenticated" class="header-search-container">
        <div class="header-search-wrapper">
          <i class='bx bx-search search-icon'></i>
          <input 
            type="text" 
            v-model="productStore.searchQuery" 
            placeholder="Rechercher un produit..." 
            ref="searchInputRef"
          />
          <div class="search-hint">
             <span>Ctrl</span>
             <span>K</span>
          </div>
        </div>
      </div>

      <!-- Navigation tabs -->
      <nav v-if="authStore.isAuthenticated" class="header-nav-container">
        <div class="header-nav-icons">
          <template v-for="link in navLinks" :key="link.path">
            <!-- Normal Links -->
            <router-link 
              v-if="link.id !== 'cart' && link.id !== 'favorites'"
              :to="link.path"
              class="header-icon-link"
              :title="link.label"
            >
              <div class="icon-wrapper">
                <i :class="link.icon"></i>
                <span v-if="link.id === 'notifications' && notifStore.unreadCount > 0" class="notif-badge">
                  {{ notifStore.unreadCount }}
                </span>
              </div>
              <span class="link-label">{{ link.label }}</span>
            </router-link>

            <!-- Favorites Dropdown -->
            <div 
              v-else-if="link.id === 'favorites'" 
              class="header-dropdown-wrapper"
              @mouseenter="showFavDropdown = true"
              @mouseleave="showFavDropdown = false"
            >
              <router-link :to="link.path" class="header-icon-link" :title="link.label">
                <div class="icon-wrapper">
                  <i :class="link.icon"></i>
                  <span v-if="favStore.count > 0" class="fav-badge">{{ favStore.count }}</span>
                </div>
                <span class="link-label">{{ link.label }}</span>
              </router-link>
              
              <transition name="slide-up">
                <div v-if="showFavDropdown" class="quick-view-dropdown">
                  <div class="dropdown-header">
                    <h4>Mes Favoris ({{ favStore.count }})</h4>
                  </div>
                  <div class="dropdown-body">
                    <div v-if="favoriteProducts.length > 0" class="mini-product-list">
                      <div v-for="p in favoriteProducts.slice(0, 5)" :key="p.id" class="mini-product-card" @click="router.push(`/product/${p.id}`)">
                        <img :src="getImageUrl(p.image)" :alt="p.name">
                        <div class="mini-product-info">
                          <span class="mini-name">{{ p.name }}</span>
                          <span class="mini-price">{{ formatPrice(p.price) }} FCFA</span>
                        </div>
                      </div>
                      <div v-if="favoriteProducts.length > 5" class="more-items-info">+ {{ favoriteProducts.length - 5 }} autres</div>
                    </div>
                    <div v-else class="dropdown-empty">
                      <i class='bx bx-heart'></i>
                      <p>Aucun favori pour le moment</p>
                    </div>
                  </div>
                  <div class="dropdown-footer">
                    <router-link :to="link.path" class="btn-view-all">Voir tout mes favoris</router-link>
                  </div>
                </div>
              </transition>
            </div>

            <!-- Cart Dropdown -->
            <div 
              v-else-if="link.id === 'cart'" 
              class="header-dropdown-wrapper"
              @mouseenter="showCartDropdown = true"
              @mouseleave="showCartDropdown = false"
            >
              <router-link :to="link.path" class="header-icon-link" :title="link.label">
                <div class="icon-wrapper">
                  <i :class="link.icon"></i>
                  <span v-if="cartTotalCount > 0" class="cart-badge">{{ cartTotalCount }}</span>
                </div>
                <span class="link-label">{{ link.label }}</span>
              </router-link>

              <transition name="slide-up">
                <div v-if="showCartDropdown" class="quick-view-dropdown">
                  <div class="dropdown-header">
                    <h4>Mon Panier ({{ cartTotalCount }})</h4>
                  </div>
                  <div class="dropdown-body">
                    <div v-if="cartStore.items.length > 0" class="mini-product-list">
                      <div v-for="item in cartStore.items.slice(0, 5)" :key="item.id" class="mini-product-card" @click="router.push('/client/cart')">
                        <img :src="getImageUrl(item.image)" :alt="item.name">
                        <div class="mini-product-info">
                          <span class="mini-name">{{ item.name }}</span>
                          <span class="mini-price">{{ item.quantity }} x {{ formatPrice(item.price) }} FCFA</span>
                        </div>
                      </div>
                      <div v-if="cartStore.items.length > 5" class="more-items-info">+ {{ cartStore.items.length - 5 }} articles</div>
                      <div class="total-preview">
                        <span>Total :</span>
                        <strong>{{ formatPrice(cartStore.totalPrice) }} FCFA</strong>
                      </div>
                    </div>
                    <div v-else class="dropdown-empty">
                      <i class='bx bx-cart'></i>
                      <p>Votre panier est vide</p>
                    </div>
                  </div>
                  <div class="dropdown-footer">
                    <router-link :to="link.path" class="btn-view-all">Aller au panier</router-link>
                    <button v-if="cartStore.items.length > 0" @click="router.push('/client/checkout')" class="btn-checkout">Commander</button>
                  </div>
                </div>
              </transition>
            </div>
          </template>
        </div>
      </nav>

      <div class="header-actions">
        <template v-if="!authStore.isAuthenticated">
          <router-link to="/login" class="header-auth-btn header-login-btn">Connexion</router-link>
          <router-link to="/register" class="header-auth-btn header-register-btn">Inscription</router-link>
        </template>

        <!-- Menu Utilisateur (Indépendant pour l'alignement à droite) -->
        <template v-else>
          <div class="header-user-wrapper" ref="userMenuRef" @click.stop="toggleUserMenu">
            <div class="header-user-profile">
              <div class="user-avatar-mini">
                <img v-if="authStore.user?.photo" :src="authStore.user.photo" alt="Avatar">
                <i v-else class='bx bx-user'></i>
              </div>
              <span class="user-name-mini">{{ authStore.user?.name }}</span>
              <i class='bx bx-chevron-down toggle-arrow' :class="{ 'rotate': showUserMenu }"></i>
            </div>

            <div v-if="showUserMenu" class="user-dropdown-menu">
              <button class="dropdown-item" @click="handleEditParams">
                <i class='bx bx-user-circle'></i> Mon Profil
              </button>
              <button class="dropdown-item" @click="themeStore.toggleTheme">
                <i :class="themeStore.dark ? 'bx bx-sun' : 'bx bx-moon'"></i> Mode {{ themeStore.dark ? 'Clair' : 'Sombre' }}
              </button>
              <hr class="dropdown-divider">
              <button class="dropdown-item logout-btn" @click="handleLogout">
                <i class='bx bx-log-out'></i> Déconnexion
              </button>
            </div>
          </div>
        </template>
      </div>
    </header>
    <div class="main-layout">
      <!-- Sidebar removed for all roles -->
      <main class="main-content">
        <router-view v-slot="{ Component, route }">
          <transition name="fade" mode="out-in">
            <component :is="Component" :key="route.path" />
          </transition>
        </router-view>
      </main>
    </div>
    <FloatingCartIcon />
  </div>
</template>

<script setup>
import NotificationToast from '@/components/NotificationToast.vue';
import FloatingCartIcon from '@/components/FloatingCartIcon.vue';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useThemeStore } from '@/stores/theme';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import { useUserNotificationsStore } from '@/stores/userNotifications';
import { useProductStore } from '@/stores/products';
import { useRouter } from 'vue-router';

import { useFavoritesStore } from '@/stores/favorites';

const themeStore = useThemeStore();
const authStore = useAuthStore();
const cartStore = useCartStore();
const favStore = useFavoritesStore();
const productStore = useProductStore();
const notifStore = useUserNotificationsStore();
const router = useRouter();

const showFavDropdown = ref(false);
const showCartDropdown = ref(false);

const favoriteProducts = computed(() => {
    return productStore.products.filter(p => favStore.isFavorite(p.id));
});

const getImageUrl = (path) => {
    if (!path) return 'https://placehold.co/100x100?text=Produit';
    if (path.startsWith('http')) return path;
    return `http://localhost:8000/storage/${path}`;
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};

const searchInputRef = ref(null);

const clientLinks = [
  { label: 'Produits', path: '/client/products', icon: 'bx bx-grid-alt' },
  { label: 'Favoris', path: '/client/favorites', icon: 'bx bx-heart', id: 'favorites' },
  { label: 'Commandes', path: '/client/orders', icon: 'bx bx-package' },
  { label: 'Notifications', path: '/client/notifications', icon: 'bx bx-bell', id: 'notifications' },
  { label: 'Panier', path: '/client/cart', icon: 'bx bx-cart', id: 'cart' },
];

const adminLinks = [
  { label: 'Tableau de bord', path: '/admin', icon: 'bx bx-shield-quarter' },
  { label: 'Produits', path: '/admin/products', icon: 'bx bx-grid-alt' },
  { label: 'Livreurs', path: '/admin/livreurs', icon: 'bx bx-cycling' },
  { label: 'Promotions', path: '/admin/promotions', icon: 'bx bx-purchase-tag-alt' },
  { label: 'Commandes', path: '/admin/orders', icon: 'bx bx-package' },
];

const livreurLinks = [
  { label: 'Dashboard', path: '/livreur', icon: 'bx bx-shield-quarter' },
  { label: 'Commandes', path: '/livreur/orders', icon: 'bx bx-package' },
];

const navLinks = computed(() => {
  if (authStore.isAdmin) return adminLinks;
  if (authStore.isLivreur) return livreurLinks;
  if (authStore.isUser) return clientLinks;
  return [];
});

const showUserMenu = ref(false);
const userMenuRef = ref(null);

const cartTotalCount = computed(() => {
    return cartStore.items.reduce((acc, item) => acc + item.quantity, 0);
});

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
};

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
    showUserMenu.value = false;
};

const handleEditParams = () => {
    if (authStore.isUser) router.push({ name: 'client-parameters' });
    else if (authStore.isLivreur) router.push({ name: 'livreur-parameters' });
    else router.push({ name: 'admin-parameters' });
    showUserMenu.value = false;
};

const closeMenu = (e) => {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        showUserMenu.value = false;
    }
};

onMounted(() => {
  themeStore.applyTheme();
  document.addEventListener('click', closeMenu);
  window.addEventListener('keydown', handleKeyDown);
  
  if (authStore.isAuthenticated && authStore.isUser) {
    notifStore.fetchNotifications();
    // Poll every 30 seconds to update the badge
    const interval = setInterval(() => {
        if (authStore.isAuthenticated && authStore.isUser) {
            notifStore.fetchNotifications();
        } else {
            clearInterval(interval);
        }
    }, 30000);
  }
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenu);
    window.removeEventListener('keydown', handleKeyDown);
});

const handleKeyDown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        searchInputRef.value?.focus();
    }
};
</script>

<style scoped>
.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.global-header {
  height: 60px;
  background-color: rgba(255, 255, 255, 0.85);
  @supports (background-color: color-mix(in srgb, white 80%, transparent)) {
    background-color: color-mix(in srgb, var(--background) 80%, transparent);
  }
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
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
  flex: 0.8;
  display: flex;
  align-items: center;
}

.header-logo img {
  height: 59px;
  width: auto;
  object-fit: contain;
  display: block;
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
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: flex-end;
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
  filter: brightness(0.9);
  border-color: var(--primary);
}

/* Search Bar Centrale */
.header-search-container {
  flex: 1.5;
  display: flex;
  justify-content: center;
  margin: 0 1rem;
}

.header-search-wrapper {
  background-color: var(--neutral);
  border: 1px solid var(--border);
  border-radius: 50px;
  padding: 0.5rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  max-width: 450px;
  transition: all 0.3s ease;
  position: relative;
}

.header-search-wrapper:focus-within {
  background-color: var(--surface);
  border-color: var(--primary);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.search-icon {
  color: #888;
  font-size: 1.2rem;
}

.header-search-wrapper input {
  background: transparent;
  border: none;
  outline: none;
  width: 100%;
  font-size: 0.95rem;
  color: var(--text);
  font-family: 'Cal Sans', sans-serif;
}

.search-hint {
  display: flex;
  gap: 3px;
  opacity: 0.4;
}

.search-hint span {
  background: rgba(0, 0, 0, 0.1);
  padding: 2px 5px;
  border-radius: 4px;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
}

[data-theme='dark'] .search-hint span {
  background: rgba(255, 255, 255, 0.1);
}

/* Navigation Centrale (Uniquement si connecté) */
.header-nav-container {
  flex: 2;
  display: flex;
  justify-content: center;
  margin: 0 1rem;
}

.header-nav-icons {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.header-icon-link {
  font-size: 1.4rem;
  color: var(--text);
  opacity: 0.7;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  position: relative;
  text-decoration: none;
  font-weight: 500;
}

.icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.link-label {
  font-size: 0.85rem;
  font-family: 'Cal Sans', sans-serif;
  white-space: nowrap;
}

.header-icon-link:hover,
.header-icon-link.router-link-active {
  color: var(--primary);
  opacity: 1;
}

.header-cart-link {
  position: relative;
}

.cart-badge {
  position: absolute;
  top: -5px;
  right: -8px;
  background-color: var(--secondary);
  color: white;
  font-size: 0.6rem;
  font-weight: 800;
  padding: 1px 4px;
  border-radius: 8px;
  min-width: 15px;
  max-height: 15px;
  text-align: center;
  border: 1.5px solid var(--surface);
  display: flex;
  align-items: center;
  justify-content: center;
}

.notif-badge {
  position: absolute;
  top: -5px;
  right: -8px;
  background-color: #ff4d4d;
  color: white;
  font-size: 0.6rem;
  font-weight: 800;
  padding: 1px 4px;
  border-radius: 8px;
  min-width: 15px;
  max-height: 15px;
  text-align: center;
  border: 1.5px solid var(--surface);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 8px rgba(255, 77, 77, 0.4);
}

/* User Profile Mini */
.header-user-wrapper {
  position: relative;
}

.header-user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.4rem 0.75rem;
  border-radius: 50px;
  cursor: pointer;
  transition: background-color 0.2s;
  border: 1px solid transparent;
}

.header-user-profile:hover {
  background-color: var(--neutral);
  border-color: var(--border);
}

.user-avatar-mini {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  color: white;
}

.user-avatar-mini img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-avatar-mini i {
  font-size: 1.2rem;
}

.user-name-mini {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--text);
}

.toggle-arrow {
  font-size: 1.2rem;
  transition: transform 0.3s ease;
  color: #888;
}

.toggle-arrow.rotate {
  transform: rotate(180deg);
}

/* User Dropdown Menu */
.user-dropdown-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 220px;
  background-color: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  padding: 0.5rem;
  z-index: 2000;
  backdrop-filter: blur(10px);
}

.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  border-radius: 8px;
  color: var(--text);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.dropdown-item:hover {
  background-color: var(--neutral);
  color: var(--primary);
}

.dropdown-item i {
  font-size: 1.2rem;
}

.logout-btn:hover {
  background-color: #ff4d4d !important;
  color: white !important;
}

.dropdown-divider {
  height: 1px;
  background-color: var(--border);
  margin: 0.5rem 0;
  border: none;
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
/* Quick View Dropdowns */
.header-dropdown-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.quick-view-dropdown {
  position: absolute;
  top: calc(100% + 5px);
  right: -50px;
  width: 320px;
  background-color: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: 0 15px 35px rgba(0,0,0,0.15);
  z-index: 2000;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.dropdown-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
  background-color: var(--background);
}

.dropdown-header h4 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text);
}

.dropdown-body {
  padding: 0.5rem;
  max-height: 400px;
  overflow-y: auto;
}

.mini-product-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.mini-product-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.mini-product-card:hover {
  background-color: var(--neutral);
}

.mini-product-card img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  object-fit: cover;
  background-color: var(--background);
}

.mini-product-info {
  display: flex;
  flex-direction: column;
  flex: 1;
  overflow: hidden;
}

.mini-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mini-price {
  font-size: 0.8rem;
  color: var(--primary);
  font-weight: 700;
  margin-top: 2px;
}

.more-items-info {
  text-align: center;
  padding: 0.5rem;
  font-size: 0.8rem;
  color: #888;
  font-style: italic;
}

.total-preview {
  margin-top: 0.5rem;
  padding: 0.75rem;
  border-top: 1px dashed var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-preview span { font-size: 0.9rem; color: #666; }
.total-preview strong { font-size: 1rem; color: var(--text); }

.dropdown-empty {
  padding: 2rem 1rem;
  text-align: center;
  color: #ccc;
}

.dropdown-empty i {
  font-size: 3rem;
  margin-bottom: 0.5rem;
  opacity: 0.3;
}

.dropdown-empty p {
  font-size: 0.9rem;
  margin: 0;
}

.dropdown-footer {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  background-color: var(--background);
  border-top: 1px solid var(--border);
}

.btn-view-all {
  width: 100%;
  padding: 0.6rem;
  border-radius: 8px;
  background-color: var(--neutral);
  color: var(--text);
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 600;
  text-align: center;
  transition: all 0.2s;
}

.btn-view-all:hover {
  background-color: var(--border);
}

.btn-checkout {
  width: 100%;
  padding: 0.75rem;
  border-radius: 8px;
  background-color: var(--primary);
  color: white;
  border: none;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: filter 0.2s;
}

.btn-checkout:hover {
  filter: brightness(1.1);
}

.fav-badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background-color: var(--primary);
    color: white;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 1px 4px;
    border-radius: 8px;
    min-width: 15px;
    max-height: 15px;
    text-align: center;
    border: 1.5px solid var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
}

.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
