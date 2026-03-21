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
  border-radius: 16px;
  overflow: hidden;
  transition: box-shadow 0.4s ease, border-color 0.3s ease;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 1;
}

.product-card:hover {
  box-shadow: 0 12px 30px rgba(0,0,0,0.06);
  border-color: var(--primary);
  z-index: 2;
}

.product-card__image {
  position: relative;
  height: 200px;
  background-color: var(--neutral);
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
  top: 16px;
  right: 16px;
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
  transition: all 0.3s ease;
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
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
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
  color: var(--text);
  font-size: 1.15rem;
  letter-spacing: -0.02em;
}

.product-card__add {
  background-color: var(--neutral);
  color: var(--text);
  border: 1px solid var(--border);
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.product-card__add i {
  font-size: 1.2rem;
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
