<template>
  <div class="product-card">
    <div class="product-card__image">
      <img :src="product.image || 'https://via.placeholder.com/300'" :alt="product.name" />
      <button 
        class="product-card__favorite" 
        @click.stop="cartStore.toggleFavorite(product.id)"
        :class="{ 'product-card__favorite--active': isFavorite }"
      >
        <i :class="isFavorite ? 'bx bxs-heart' : 'bx bx-heart'"></i>
      </button>
    </div>
    <div class="product-card__content">
      <div class="product-card__header">
        <h3 class="product-card__title">{{ product.name }}</h3>
        <p class="product-card__category">{{ product.category?.label }}</p>
      </div>
      <div class="product-card__footer">
        <span class="product-card__price">{{ formatPrice(product.price) }} FCFA</span>
        <button class="product-card__add" @click.prevent="cartStore.addToCart(product)" aria-label="Ajouter au panier">
          <i class='bx bx-plus'></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useCartStore } from '@/stores/cart';

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
});

const cartStore = useCartStore();

const isFavorite = computed(() => {
    return cartStore.favorites.some(f => f.product_id === props.product.id);
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};
</script>

<style scoped>
.product-card {
  background-color: var(--background);
  border: 1px solid var(--border);
  border-radius: 24px;
  overflow: hidden;
  transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s ease, border-color 0.3s ease;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 1;
}

.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 24px 48px rgba(0,0,0,0.06);
  border-color: var(--border);
  z-index: 2;
}

.product-card__image {
  position: relative;
  height: 260px;
  background-color: var(--neutral);
  overflow: hidden;
  padding: 1rem;
}

.product-card__image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 16px;
  transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.product-card:hover .product-card__image img {
  transform: scale(1.06);
}

.product-card__favorite {
  position: absolute;
  top: 24px;
  right: 24px;
  background-color: var(--background);
  border: 1px solid var(--border);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text);
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
  z-index: 10;
}

.product-card__favorite:hover {
  transform: scale(1.1);
  color: #ff4d4d;
  border-color: #ff4d4d;
}

.product-card__favorite--active {
  color: #ff4d4d;
  background-color: rgba(255, 77, 77, 0.1);
  border-color: rgba(255, 77, 77, 0.2);
}

.product-card__favorite--active i {
  transform: scale(1.1);
}

.product-card__content {
  padding: 1.75rem;
  display: flex;
  flex-direction: column;
  flex: 1;
  background-color: var(--background);
}

.product-card__header {
  margin-bottom: 2rem;
}

.product-card__title {
  font-size: 1.2rem;
  font-weight: 700;
  margin: 0 0 0.4rem;
  color: var(--text);
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-card__category {
  font-size: 0.85rem;
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
  color: var(--text);
  font-size: 1.35rem;
  letter-spacing: -0.02em;
}

.product-card__add {
  background-color: var(--neutral);
  color: var(--text);
  border: 1px solid var(--border);
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.product-card__add i {
  font-size: 1.4rem;
  transition: transform 0.3s ease;
}

.product-card__add:hover {
  background-color: var(--primary);
  color: #FFFFFF;
  border-color: var(--primary);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.1);
}

.product-card__add:hover i {
  transform: rotate(90deg);
}

[data-theme='dark'] .product-card__favorite {
  background-color: var(--surface);
}

[data-theme='dark'] .product-card__image {
  background-color: #111;
}
</style>
