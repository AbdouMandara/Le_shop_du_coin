<template>
  <div class="favorites-page">
    <header class="page-header">
        <h1>Mes Favoris</h1>
        <p>Retrouvez tous les produits que vous avez aimés.</p>
    </header>

    <div v-if="favoriteProducts.length === 0" class="empty">
        <i class='bx bx-heart'></i>
        <p>Vous n'avez pas encore de favoris.</p>
        <router-link to="/products" class="btn-primary">Découvrir nos produits</router-link>
    </div>

    <div v-else class="product-grid">
        <ProductCard 
            v-for="product in favoriteProducts" 
            :key="product.id" 
            :product="product"
        />
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useProductStore } from '@/stores/products';
import { useFavoritesStore } from '@/stores/favorites';
import ProductCard from '@/components/ProductCard.vue';

const productStore = useProductStore();
const favStore = useFavoritesStore();

const favoriteProducts = computed(() => {
    return productStore.products.filter(p => favStore.isFavorite(p.id));
});

onMounted(() => {
    if (productStore.products.length === 0) {
        productStore.fetchProducts();
    }
});
</script>

<style scoped>
.favorites-page {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 3rem;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
}

.page-header p {
    color: #888;
    margin-top: 0.5rem;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 2rem;
}

.empty {
    text-align: center;
    padding: 6rem 2rem;
    background-color: var(--surface);
    border-radius: 12px;
    border: 1px dashed var(--border);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.empty i {
    font-size: 4rem;
    color: #DDD;
}

.empty p {
    font-size: 1.2rem;
    color: #888;
    margin: 0;
}

.btn-primary {
    background-color: var(--primary);
    color: #FFFFFF;
    padding: 0.85rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
}
</style>
