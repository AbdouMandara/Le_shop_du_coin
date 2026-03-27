<template>
  <div class="floating-cart-container" v-if="isVisible">
    <!-- Floating Button -->
    <button 
      class="cart-btn" 
      @click="toggleDrawer" 
      :class="{ 'active': isDrawerOpen }"
      aria-label="Voir le panier"
    >
      <i class='bx bx-cart'></i>
      <transition name="scale">
        <span v-if="cartStore.itemCount > 0" class="cart-badge">{{ cartStore.itemCount }}</span>
      </transition>
    </button>

    <!-- Overlay -->
    <transition name="fade">
      <div v-if="isDrawerOpen" class="cart-overlay" @click="toggleDrawer"></div>
    </transition>

    <!-- Drawer -->
    <transition name="slide-left">
      <div v-if="isDrawerOpen" class="cart-drawer">
        <div class="drawer-header">
          <h3>Votre Panier</h3>
          <button @click="toggleDrawer" class="close-btn"><i class='bx bx-x'></i></button>
        </div>

        <div class="drawer-content">
          <div v-if="cartStore.items.length === 0" class="empty-cart">
            <i class='bx bx-cart-alt'></i>
            <p>Votre panier est vide</p>
            <router-link to="/products" class="shop-now-btn" @click="toggleDrawer">
              Découvrir nos produits
            </router-link>
          </div>
          <div v-else class="cart-items">
            <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
              <div class="item-img-container">
                <img :src="item.image || '/placeholder.png'" :alt="item.name">
              </div>
              <div class="item-info">
                <span class="item-name">{{ item.name }}</span>
                <div class="item-meta">
                  <span class="item-price">{{ item.price }} FCFA</span>
                  <span class="item-quantity">x {{ item.quantity }}</span>
                </div>
              </div>
              <button @click="cartStore.removeFromCart(item.id)" class="remove-btn" title="Supprimer">
                <i class='bx bx-trash'></i>
              </button>
            </div>
          </div>
        </div>

        <div class="drawer-footer" v-if="cartStore.items.length > 0">
          <div class="total-section">
            <span class="total-label">Total</span>
            <span class="total-amount">{{ cartStore.totalPrice }} FCFA</span>
          </div>
          <div class="drawer-actions">
            <router-link to="/client/cart" class="btn-secondary" @click="toggleDrawer">
              Voir le panier complet
            </router-link>
            <button class="btn-primary" @click="checkout">
              Commander
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const isDrawerOpen = ref(false);

const isVisible = computed(() => {
  // Visible on home if not authenticated
  if (route.name === 'home' && !authStore.isAuthenticated) return true;
  
  // Visible on client products
  if (route.name === 'client-products' || route.path === '/client/products') return true;
  
  // Visible on public products
  if (route.name === 'products' || route.path === '/products') return true;
  
  return false;
});

const toggleDrawer = () => {
  isDrawerOpen.value = !isDrawerOpen.value;
};

const checkout = () => {
  toggleDrawer();
  if (!authStore.isAuthenticated) {
    router.push('/login');
  } else {
    router.push('/client/cart');
  }
};
</script>

<style scoped>
.floating-cart-container {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
}

.cart-btn {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--primary);
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
}

.cart-btn:hover {
  background: var(--secondary);
}

.cart-btn.active {
  transform: scale(0.9);
}

.cart-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: var(--secondary);
  color: white;
  font-size: 0.75rem;
  font-weight: bold;
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  border: 2px solid var(--background);
  min-width: 20px;
  text-align: center;
}

/* Drawer Styles */
.cart-drawer {
  position: fixed;
  top: 0;
  right: 0;
  width: 350px;
  height: 100vh;
  background: var(--surface);
  box-shadow: -5px 0 25px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  z-index: 1002;
}

.drawer-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.drawer-header h3 {
  margin: 0;
  font-family: 'Cabin', sans-serif;
  color: var(--text);
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.close-btn:hover {
  color: var(--secondary);
}

.drawer-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.empty-cart {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text);
  opacity: 0.7;
  gap: 1rem;
}

.empty-cart i {
  font-size: 4rem;
}

.shop-now-btn {
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  background: var(--primary);
  color: white;
  border-radius: 8px;
  font-weight: 500;
  transition: background 0.2s;
}

.shop-now-btn:hover {
  background: var(--secondary);
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: var(--neutral);
  border-radius: 12px;
  transition: transform 0.2s;
}

.cart-item:hover {
  transform: translateX(-5px);
}

.item-img-container {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

.item-img-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.item-name {
  font-weight: 600;
  color: var(--text);
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.item-meta {
  display: flex;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--text);
  opacity: 0.8;
}

.item-price {
  font-weight: 600;
  color: var(--secondary);
}

.remove-btn {
  background: none;
  border: none;
  color: #ff4d4d;
  cursor: pointer;
  padding: 0.5rem;
  font-size: 1.1rem;
  transition: transform 0.2s;
}

.remove-btn:hover {
  transform: scale(1.2);
}

.drawer-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--border);
  background: var(--surface);
}

.total-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.total-label {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text);
}

.total-amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--secondary);
}

.drawer-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-primary, .btn-secondary {
  padding: 0.8rem;
  border-radius: 10px;
  font-weight: 600;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  width: 100%;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover {
  background: var(--secondary);
  box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

.btn-secondary {
  background: transparent;
  color: var(--text);
  border: 1px solid var(--border);
}

.btn-secondary:hover {
  background: var(--neutral);
}

.cart-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(4px);
  z-index: 1001;
}

/* Animations */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.3s ease-out; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(100%); }

.scale-enter-active, .scale-leave-active { transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.scale-enter-from, .scale-leave-to { transform: scale(0); }

@media (max-width: 480px) {
  .cart-drawer {
    width: 100%;
  }
}
</style>
