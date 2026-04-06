<template>
  <div class="floating-cart-container" v-if="isVisible" ref="containerRef">
    <!-- Quick View Panel -->
    <transition name="slide-fade">
      <div v-if="showPreview && cartStore.items.length > 0" class="cart-quick-view">
        <div class="quick-view-header">
          <h3>Mon Panier</h3>
          <button class="btn-close-mini" @click="showPreview = false">&times;</button>
        </div>
        <div class="quick-view-items">
          <div v-for="item in cartStore.items" :key="item.id" class="q-item">
            <img :src="getImageUrl(item.image)" :alt="item.name">
            <div class="q-info">
              <span class="q-name">{{ item.name }}</span>
              <span class="q-price">{{ item.quantity }} x {{ formatPrice(item.price) }} FCFA</span>
            </div>
            <button class="btn-remove-mini" @click="cartStore.removeFromCart(item.id)">
              <i class='bx bx-trash'></i>
            </button>
          </div>
        </div>
        <div class="quick-view-footer">
          <div class="q-total">
            <span>Total:</span>
            <strong>{{ formatPrice(cartStore.totalPrice) }} FCFA</strong>
          </div>
          <div class="q-actions">
            <button class="btn-full-cart" @click="goToCart">Voir le panier</button>
            <button class="btn-checkout-mini" @click="goToCheckout">Commander</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Floating Button -->
    <button 
      class="cart-btn" 
      @click="togglePreview" 
      aria-label="Voir le panier"
      :class="{ 'active': showPreview }"
    >
      <i class='bx' :class="showPreview ? 'bx-x' : 'bx-cart'"></i>
      <transition name="scale">
        <span v-if="cartStore.itemCount > 0" class="cart-badge">{{ cartStore.itemCount }}</span>
      </transition>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const showPreview = ref(false);
const containerRef = ref(null);

const isVisible = computed(() => {
  if (route.name === 'home' && !authStore.isAuthenticated) return true;
  if (route.name === 'client-products' || route.path === '/client/products') return true;
  if (route.name === 'products' || route.path === '/products') return true;
  return false;
});

const togglePreview = () => {
  if (cartStore.items.length === 0) {
    goToCart();
  } else {
    showPreview.value = !showPreview.value;
  }
};

const goToCart = () => {
  showPreview.value = false;
  if (!authStore.isAuthenticated) {
    router.push('/login');
  } else {
    router.push('/client/cart');
  }
};

const goToCheckout = () => {
  showPreview.value = false;
  router.push('/client/checkout');
};

const getImageUrl = (path) => {
    if (!path) return 'https://placehold.co/50x50?text=Produit';
    if (path.startsWith('http')) return path;
    return `http://localhost:8000/storage/${path}`;
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};

// Fermer au clic extérieur
const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    showPreview.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
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
  box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
  position: relative;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.cart-btn:hover {
  transform: translateY(-5px) scale(1.05);
  background: var(--secondary);
}

.cart-btn.active {
  background: #333;
  transform: rotate(90deg);
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

/* Quick View Panel */
.cart-quick-view {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 320px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 15px 40px rgba(0,0,0,0.2);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.quick-view-header {
  padding: 1rem;
  background: var(--background);
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border);
}

.quick-view-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
}

.btn-close-mini {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #888;
}

.quick-view-items {
  max-height: 300px;
  overflow-y: auto;
  padding: 0.5rem;
}

.q-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.75rem;
  border-radius: 12px;
  transition: background 0.2s;
}

.q-item:hover {
  background: var(--neutral);
}

.q-item img {
  width: 45px;
  height: 45px;
  border-radius: 8px;
  object-fit: cover;
}

.q-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.q-name {
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.q-price {
  font-size: 0.8rem;
  color: var(--primary);
  font-weight: 700;
}

.btn-remove-mini {
  background: none;
  border: none;
  color: #ff4d4d;
  cursor: pointer;
  opacity: 0.3;
  transition: opacity 0.2s;
}

.q-item:hover .btn-remove-mini {
  opacity: 1;
}

.quick-view-footer {
  padding: 1rem;
  background: var(--background);
  border-top: 1px solid var(--border);
}

.q-total {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
  font-size: 0.95rem;
}

.q-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-full-cart {
  flex: 1;
  padding: 0.6rem;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--neutral);
  font-weight: 600;
  font-size: 0.8rem;
  cursor: pointer;
}

.btn-checkout-mini {
  flex: 1;
  padding: 0.6rem;
  border-radius: 10px;
  border: none;
  background: var(--primary);
  color: white;
  font-weight: 700;
  font-size: 0.8rem;
  cursor: pointer;
}

/* Animations */
.scale-enter-active, .scale-leave-active { 
  transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
}
.scale-enter-from, .scale-leave-to { 
  transform: scale(0); 
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>
