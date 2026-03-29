<template>
  <div class="floating-cart-container" v-if="isVisible">
    <!-- Floating Button -->
    <button 
      class="cart-btn" 
      @click="goToCart" 
      aria-label="Voir le panier"
    >
      <i class='bx bx-cart'></i>
      <transition name="scale">
        <span v-if="cartStore.itemCount > 0" class="cart-badge">{{ cartStore.itemCount }}</span>
      </transition>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const isVisible = computed(() => {
  // Visible on home if not authenticated
  if (route.name === 'home' && !authStore.isAuthenticated) return true;
  
  // Visible on client products
  if (route.name === 'client-products' || route.path === '/client/products') return true;
  
  // Visible on public products
  if (route.name === 'products' || route.path === '/products') return true;
  
  return false;
});

const goToCart = () => {
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
  position: relative;
  transition: transform 0.2s ease, background-color 0.2s ease;
}

.cart-btn:hover {
  background: var(--secondary);
  transform: scale(1.05);
}

.cart-btn:active {
  transform: scale(0.95);
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
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Animations */
.scale-enter-active, .scale-leave-active { 
  transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
}
.scale-enter-from, .scale-leave-to { 
  transform: scale(0); 
}
</style>
