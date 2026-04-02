<template>
  <div class="product-detail-view" v-if="product">
    <div class="main-container">
      <!-- Header / Nav -->
      <div class="nav-header">
        <button @click="$router.back()" class="back-link">
          <i class='bx bx-left-arrow-alt'></i> Retour
        </button>
      </div>

      <div class="product-grid">
        <!-- Image Section -->
        <div class="image-gallery">
          <div class="main-image-container">
            <img :src="getProductImage" :alt="product.name" class="main-image">
            <button 
              class="favorite-toggle" 
              @click="favStore.toggleFavorite(product.id)"
              :class="{ 'active': isFavorite }"
            >
              <i :class="isFavorite ? 'bx bxs-heart' : 'bx bx-heart'"></i>
            </button>
          </div>
        </div>

        <!-- Info Section -->
        <div class="product-info">
          <div class="info-top">
            <span class="category-tag">{{ product.category.label || 'Général' }}</span>
            <div class="rating-block">
              <div class="stars">
                <i v-for="i in 5" :key="i" class='bx' 
                   :class="getStarClass(i, product.avg_rating || product.rating || 0)"></i>
              </div>
              <span class="rating-text">{{ (product.avg_rating || product.rating || 0).toFixed(1) }}</span>
            </div>
          </div>

          <h1 class="product-name">{{ product.name }}</h1>

          <div class="price-section">
            <div class="prices">
              <span v-if="product.original_price > product.price" class="old-price">
                {{ formatPrice(product.original_price) }} FCFA
              </span>
              <span class="main-price">{{ formatPrice(product.price) }} FCFA</span>
            </div>
          </div>

          <div class="divider"></div>

          <div class="description">
            <h3>Description</h3>
            <p>{{ product.description || "Ce produit est soigneusement sélectionné pour sa qualité et sa durabilité. Profitez d'une expérience d'achat unique." }}</p>
          </div>

          <div class="divider"></div>

          <div class="cta-section">
            <div class="qty-selection" v-if="cartItem">
              <button @click="updateQty(-1)" :disabled="cartItem.quantity <= 1" class="qty-btn">
                <i class='bx bx-minus'></i>
              </button>
              <span class="qty-value">{{ cartItem.quantity }}</span>
              <button @click="updateQty(1)" class="qty-btn">
                <i class='bx bx-plus'></i>
              </button>
            </div>

            <button v-if="!cartItem" class="add-btn" @click="cartStore.addToCart(product)">
              <i class='bx bx-cart-add'></i> Ajouter au panier
            </button>

          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Loading State -->
  <div v-else-if="loading" class="loading-view">
    <div class="loader-spinner"></div>
    <p>Chargement du produit...</p>
  </div>

  <!-- Error State -->
  <div v-else class="error-view">
    <i class='bx bx-error-circle'></i>
    <h2>Oups !</h2>
    <p>Nous n'avons pas pu charger ce produit.</p>
    <router-link to="/" class="home-btn">Retour à l'accueil</router-link>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useProductStore } from '@/stores/products';
import { useCartStore } from '@/stores/cart';
import { useFavoritesStore } from '@/stores/favorites';

const route = useRoute();
const productStore = useProductStore();
const cartStore = useCartStore();
const favStore = useFavoritesStore();

const product = computed(() => productStore.currentProduct);
const loading = ref(true);

onMounted(async () => {
    try {
        await productStore.fetchProduct(route.params.id);
    } catch (err) {
        console.error("Erreur lors du chargement du produit:", err);
    } finally {
        loading.value = false;
    }
});

const isFavorite = computed(() => {
    if (!product.value) return false;
    return favStore.isFavorite(product.value.id);
});

const cartItem = computed(() => {
    if (!product.value) return null;
    return cartStore.items.find(i => i.id === product.value.id);
});

const updateQty = (diff) => {
    if (cartItem.value) {
        const newQty = cartItem.value.quantity + diff;
        if (newQty > 0) {
            cartItem.value.quantity = newQty;
            cartStore.saveCart();
        }
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};

const getProductImage = computed(() => {
    if (!product.value) return '';
    const path = product.value.image;
    if (!path) return 'https://placehold.co/600x600?text=Produit';
    if (path.startsWith('http')) return path;
    return `/storage/${path}`;
});

const getStarClass = (index, rating) => {
    if (rating >= index) return 'bxs-star';
    if (rating >= index - 0.5) return 'bx-star-half';
    return 'bx-star';
};
</script>

<style scoped>
.product-detail-view {
  min-height: calc(100vh - 60px);
  background-color: var(--surface);
  padding: 2rem 0;
}

.main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.nav-header {
  margin-bottom: 2rem;
}

.back-link {
  background: none;
  border: none;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  margin-left: -1rem;
}

.back-link i {
  font-size: 1.5rem;
  transition: transform 0.3s ease;
}

.back-link:hover {
  color: var(--primary);
  background-color: var(--neutral);
}

.back-link:hover i {
  transform: translateX(-5px);
}

.product-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: start;
}

/* Image Section */
.image-gallery {
  position: sticky;
  top: 100px;
}

.main-image-container {
  background-color: white;
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  aspect-ratio: 1;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  border: 1px solid var(--border);
}

.main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.favorite-toggle {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: white;
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #666;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.favorite-toggle.active {
  color: #ff4d4d;
  transform: scale(1.1);
}

/* Info Section */
.product-info {
  display: flex;
  flex-direction: column;
  padding-top: 4.5em;
}

.info-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.category-tag {
  background-color: var(--neutral);
  color: #888;
  padding: 0.4rem 1rem;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.rating-block {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stars {
  color: #FFB800;
  font-size: 1.1rem;
}

.rating-text {
  font-weight: 700;
  color: #888;
}

.product-name {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  color: var(--text);
  line-height: 1.1;
}

.price-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.prices {
  display: flex;
  flex-direction: row-reverse;
  align-items: center;
  gap : 0.75em;
}

.main-price {
  font-size: 2.25rem;
  font-weight: 800;
  color: var(--primary);
}

.old-price {
  font-size: 1.1rem;
  color: #999;
  text-decoration: line-through;
  font-weight: 500;
}

.discount-badge {
  background-color: var(--secondary);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 800;
  font-size: 0.85rem;
}

.divider {
  height: 1px;
  background-color: var(--border);
}

.description h3 {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.description p {
  color: #666;
  line-height: 1.8;
  font-size: 1.05rem;
}

.cta-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-top: 2rem;
}

.qty-selection {
  display: flex;
  justify-content: space-between;
  width: 100%;
  align-items: center;
  gap: 1rem;
  background-color: var(--neutral);
  padding: 0.5rem;
  border-radius: 50px;
  border: 1px solid var(--border);
}

.qty-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid var(--border);
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.qty-btn:hover:not(:disabled) {
  background-color: var(--primary);
  color: white;
}

.qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.qty-value {
  font-weight: 700;
  font-size: 1.2rem;
  min-width: 30px;
  text-align: center;
}

.add-btn {
  flex: 1;
  background-color: var(--primary);
  color: white;
  border: none;
  height: 56px;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  cursor: pointer;
}

.already-in-cart {
  flex: 1;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  color: #2ecc71;
  font-weight: 700;
  font-size: 1.1rem;
  background-color: #e8f8f0;
  border-radius: 50px;
}

.trust-badges {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  text-align: center;
  padding: 1rem;
  background-color: var(--neutral);
  border-radius: 12px;
  border: 1px solid var(--border);
}

.badge i {
  font-size: 1.5rem;
  color: var(--primary);
}

.badge span {
  font-size: 0.75rem;
  font-weight: 600;
  color: #666;
}

/* States */
.loading-view, .error-view {
  min-height: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  text-align: center;
}

.loader-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--neutral);
  border-top: 4px solid var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.error-view i {
  font-size: 4rem;
  color: #ff4d4d;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 992px) {
  .product-grid {
    grid-template-columns: 1fr;
    gap: 3rem;
  }
  
  .image-gallery {
    position: relative;
    top: 0;
  }
}

@media (max-width: 576px) {
  .product-name {
    font-size: 1.75rem;
  }
  
  .cta-section {
    flex-direction: column;
  }
  
  .qty-selection, .add-btn, .already-in-cart {
    width: 100%;
  }
}
</style>
