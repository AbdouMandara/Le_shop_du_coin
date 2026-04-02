<template>
  <div v-if="product" class="product-card">
    <div class="product-card__image">
      <div class="product-card__image-wrapper">
        <img v-if="product.image" :src="getProductImage" :alt="product.name" />
        <i v-else class='bx bx-image'></i>
      </div>
      <button 
        class="product-card__favorite" 
        @click.stop="favStore.toggleFavorite(product.id)"
        :class="{ 'product-card__favorite--active': isFavorite }"
      >
        <i :class="isFavorite ? 'bx bxs-heart' : 'bx bx-heart'"></i>
      </button>
    </div>
    <div class="product-card__content">
      <div class="product-card__header">
        <h3 class="product-card__title">{{ product.name.length > 30 ? product.name.slice(0, 30) + '...' : product.name }}</h3>
        <div class="product-card__category-price">
          <div class="product-card__price-wrapper">
            <span v-if="product.original_price && product.original_price > product.price" class="product-card__price--old">
              {{ formatPrice(product.original_price) }}
            </span>
            <span class="product-card__price">{{ formatPrice(product.price) }} FCFA</span>
          </div>
          <div v-if="product.original_price && product.original_price > product.price" class="product-card__promo-badge">
              - {{ formatPrice(product.active_promotion.value) }} %
          </div>
        </div>
        <div class="product-card__rating">
          <div class="product-card__stars">
            <i v-for="i in 5" :key="i" class='bx' 
               :class="getStarClass(i, product.avg_rating || product.rating || 0)"></i>
          </div>
          <span class="product-card__rating-value">{{ (product.avg_rating || product.rating || 0).toFixed(1) }} / 5</span>
        </div>
      </div>
      <div class="product-card__footer">
        <div v-if="cartItem" class="product-card__qty-controls">
          <button @click.prevent="updateQty(-1)" :disabled="cartItem.quantity <= 1"><i class='bx bx-minus'></i></button>
          <span>{{ cartItem.quantity }}</span>
          <button @click.prevent="updateQty(1)"><i class='bx bx-plus'></i></button>
        </div>
        <button v-else class="product-card__add-btn" @click.prevent="cartStore.addToCart(product)">
          <i class='bx bx-cart-add'></i> Ajouter au panier
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useFavoritesStore } from '@/stores/favorites';

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
});

const cartStore = useCartStore();
const favStore = useFavoritesStore();

const isFavorite = computed(() => {
    if (!props.product) return false;
    return favStore.isFavorite(props.product.id);
});

const cartItem = computed(() => {
    if (!props.product) return null;
    return cartStore.items.find(i => i.id === props.product.id);
});

const updateQty = (diff) => {
    if (cartItem.value) {
        const newQty = cartItem.value.quantity + diff;
        if (newQty <= 0) {
            cartStore.removeFromCart(props.product.id);
        } else {
            cartItem.value.quantity = newQty;
            cartStore.saveCart();
        }
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};

const getProductImage = computed(() => {
    if (!props.product) return 'https://placehold.co/300x300?text=Produit';
    const path = props.product.image;
    if (!path) return 'https://placehold.co/300x300?text=Produit';
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
.product-card {
  background-color: var(--background);
  border: 1px solid var(--border);
  border-radius: 16px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 1;
}

.product-card:hover {
  z-index: 2;
}

.product-card__image {
  position: relative;
  height: 200px;
  overflow: hidden;
  padding: 1rem;
}

.product-card__image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 12px;
}

.product-card__favorite {
  position: absolute;
  top: 22px;
  right: 22px;
  background-color: #FFFFFF;
  border: 1px solid var(--border);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  z-index: 10;
}

.product-card__favorite:hover {
  color: #ff4d4d;
}

.product-card__favorite--active {
  color: #ff4d4d;
}

.product-card__content {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex: 1;
  background-color: var(--background);
}

.product-card__header {
  margin-bottom: 1.25rem;
}

.product-card__title {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 0.4rem;
  color: var(--text);
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-card__rating {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.8rem;
}

.product-card__stars {
  display: flex;
  color: #FFB800; 
  font-size: 0.9rem;
}

.product-card__rating-value {
  font-size: 0.85rem;
  font-weight: 700;
  color: #888;
}

.product-card__category-price{
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  
}
.product-card__category {
  font-size: 0.8rem;
  color: #888;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}

.product-card__footer {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.product-card__price {
    font-weight: 800;
    color: var(--primary);
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.product-card__price--old {
  font-size: 0.95rem;
  color: #999;
  text-decoration: line-through;
  font-weight: 500;
  margin-right: 8px;
}

.product-card__promo-badge {
  background-color: var(--secondary);
  color: white;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  z-index: 10;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.product-card__price-wrapper {
  display: flex;
  flex-direction: column-reverse;
}

.product-card__add-btn {
  background-color: var(--primary);
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
  transition: all 0.3s ease;
}

.product-card__add-btn i {
  font-size: 1.3rem;
}

.product-card__add-btn:hover {
  filter: brightness(0.9);
  box-shadow: 0 6px 20px rgba(255, 107, 53, 0.3);
}

.product-card__qty-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: transparent;
  border: 2px solid var(--border);
  border-radius: 50px;
  width: 100%;
  height: 46px;
  overflow: hidden;
  padding: 0 0.35em;
}

.product-card__qty-controls button {
  background: transparent;
  border: 1px solid var(--border);
  border-radius : 50%;
  padding: 0.25em;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.2rem;
  color: var(--text);
}

.product-card__qty-controls button:hover:not(:disabled) {
  background-color: var(--primary);
  color: white;
}

.product-card__qty-controls button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.product-card__qty-controls span {
  font-weight: 700;
  color: var(--text);
  font-size: 1.1rem;
  flex: 1;
  text-align: center;
}

[data-theme='dark'] .product-card__favorite {
  background-color: var(--surface);
}

[data-theme='dark'] .product-card__image {
  background-color: #111;
}

.product-card__image-wrapper {
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, var(--neutral) 25%, var(--border) 50%, var(--neutral) 75%);
  background-size: 200% 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
}

.product-card__image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  top: 0;
  left: 0;
}

.product-card__image-wrapper i {
  font-size: 3.5rem;
  color: var(--border);
  opacity: 0.5;
}

</style>