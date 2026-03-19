<template>
  <div class="products-page">
    <div class="products-sidebar">
        <h3>Catégories</h3>
        <ul class="category-list">
            <li 
                :class="{ active: !selectedCategory }" 
                @click="selectedCategory = null"
            >
                Tous les produits
            </li>
            <li 
                v-for="cat in productStore.categories" 
                :key="cat.id"
                :class="{ active: selectedCategory === cat.id }"
                @click="selectedCategory = cat.id"
            >
                {{ cat.label }}
            </li>
        </ul>
    </div>

    <div class="products-main">
        <header class="products-header">
            <h1>Nos Produits</h1>
            <div class="search-bar">
                <i class='bx bx-search'></i>
                <input type="text" v-model="searchQuery" placeholder="Rechercher un produit..." />
            </div>
        </header>

        <div v-if="productStore.loading" class="loading">
            <i class='bx bx-loader-alt bx-spin'></i> Chargement...
        </div>

        <div v-else class="product-grid">
            <ProductCard 
                v-for="product in filteredProducts" 
                :key="product.id" 
                :product="product"
            />
        </div>

        <div v-if="!productStore.loading && filteredProducts.length === 0" class="empty">
            Aucun produit trouvé.
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useProductStore } from '@/stores/products';
import ProductCard from '@/components/ProductCard.vue';

const productStore = useProductStore();
const selectedCategory = ref(null);
const searchQuery = ref('');

const filteredProducts = computed(() => {
    return productStore.products.filter(p => {
        const matchesCategory = !selectedCategory.value || p.category_id === selectedCategory.value;
        const matchesSearch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});

onMounted(() => {
    productStore.fetchProducts();
    productStore.fetchCategories();
});
</script>

<style scoped>
.products-page {
    display: flex;
    gap: 2rem;
    padding: 2rem;
}

.products-sidebar {
    width: 240px;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.products-sidebar h3 {
    margin-top: 0;
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}

.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.category-list li {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    color: #666;
}

.category-list li:hover {
    background-color: var(--neutral);
    color: var(--text);
}

.category-list li.active {
    background-color: var(--primary);
    color: #FFFFFF;
}

.products-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.products-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.products-header h1 {
    margin: 0;
    font-size: 2rem;
}

.search-bar {
    position: relative;
    display: flex;
    align-items: center;
    min-width: 300px;
}

.search-bar i {
    position: absolute;
    left: 1rem;
    color: #999;
}

.search-bar input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 1px solid var(--border);
    border-radius: 10px;
    background-color: var(--surface);
    color: var(--text);
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 2rem;
}

.loading, .empty {
    text-align: center;
    padding: 5rem;
    font-size: 1.2rem;
    color: #888;
}

[data-theme='dark'] .category-list li {
    color: #AAA;
}

[data-theme='dark'] .category-list li:hover {
    color: #FFF;
}
</style>
