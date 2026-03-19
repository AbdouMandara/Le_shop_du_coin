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
      <h3 class="product-card__title">{{ product.name }}</h3>
      <p class="product-card__category">{{ product.category?.label }}</p>
      <div class="product-card__footer">
        <span class="product-card__price">{{ product.price }} FCFA</span>
        <button class="product-card__add" @click="cartStore.addToCart(product)">
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
</script>

<style scoped>
.product-card {
  background-color: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
    /* No transforms as requested, but subtle shadow is okay usually. 
       User said "Pas d'effets de surbrillance/ombre excessifs", so we keep it minimal. */
    border-color: var(--secondary);
}

.product-card__image {
  position: relative;
  height: 200px;
  background-color: var(--neutral);
}

.product-card__image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-card__favorite {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: #FFFFFF;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: all 0.2s;
}

.product-card__favorite--active {
  color: #ff4d4d;
}

.product-card__content {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.product-card__title {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  color: var(--text);
}

.product-card__category {
  font-size: 0.85rem;
  color: #888;
  margin: 0;
}

.product-card__footer {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 1rem;
}

.product-card__price {
  font-weight: 700;
  color: var(--secondary);
  font-size: 1.1rem;
}

.product-card__add {
  background-color: var(--primary);
  color: #FFFFFF;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: opacity 0.2s;
}

.product-card__add:hover {
  opacity: 0.9;
}
</style>
