<template>
  <div class="products-page">
    <div class="products-main">
        <header class="products-header">
            <div class="header-title-bar">
                <h1>Nos Produits</h1>
                <div class="search-bar">
                    <i class='bx bx-search'></i>
                    <input type="text" v-model="searchQuery" placeholder="Rechercher un produit..." />
                </div>
            </div>
            
            <div class="products-controls-row">
                <div class="categories-inline">
                    <button 
                        class="cat-chip" 
                        :class="{ active: !selectedCategory }" 
                        @click="selectedCategory = null"
                    >
                        Tous les produits
                    </button>
                    <button 
                        v-for="cat in topCategories" 
                        :key="cat.id"
                        class="cat-chip"
                        :class="{ active: selectedCategory === cat.id }"
                        @click="selectedCategory = cat.id"
                    >
                        {{ cat.label }}
                    </button>
                </div>

                <div class="filter-actions">
                    <div class="filter-dropdown-wrapper" ref="filterMenuRef">
                        <button class="action-btn" @click.stop="showFilterModal = !showFilterModal">
                            <i class='bx bx-filter-alt'></i> Filtres
                        </button>
                        
                        <!-- Dropdown Filtres -->
                        <div v-if="showFilterModal" class="filter-dropdown" @click.stop>
                            <div class="filter-dropdown-header">
                                <h3>Filtres détaillés</h3>
                            </div>
                            
                            <div class="filter-dropdown-body">
                                <div class="filter-group">
                                    <label>Critère de tri</label>
                                    <select v-model="sortBy" class="filter-input">
                                        <option value="date">Date d'ajout</option>
                                        <option value="price">Prix</option>
                                        <option value="name">Alphabétique</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sort-order-box">
                        <span class="sort-label">Classé par :</span>
                        <select v-model="sortOrder" class="sort-select">
                            <option value="desc">Décroissant</option>
                            <option value="asc">Croissant</option>
                        </select>
                    </div>
                </div>
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
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useProductStore } from '@/stores/products';
import ProductCard from '@/components/ProductCard.vue';

const productStore = useProductStore();
const selectedCategory = ref(null);
const searchQuery = ref('');

const showFilterModal = ref(false);
const sortBy = ref('date');
const sortOrder = ref('desc');
const filterInStock = ref(false);
const filterMenuRef = ref(null);

const closeFilterMenu = (e) => {
    if (filterMenuRef.value && !filterMenuRef.value.contains(e.target)) {
        showFilterModal.value = false;
    }
};

const topCategories = computed(() => {
    const counts = {};
    productStore.products.forEach(p => {
        counts[p.category_id] = (counts[p.category_id] || 0) + 1;
    });

    return [...productStore.categories]
        .sort((a, b) => (counts[b.id] || 0) - (counts[a.id] || 0))
        .slice(0, 3);
});

const filteredProducts = computed(() => {
    // 1. Filtrer
    let result = productStore.products.filter(p => {
        const matchesCategory = !selectedCategory.value || p.category_id === selectedCategory.value;
        const matchesSearch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStock = !filterInStock.value || p.stock > 0;
        return matchesCategory && matchesSearch && matchesStock;
    });

    // 2. Trier
    result.sort((a, b) => {
        let cmp = 0;
        if (sortBy.value === 'price') {
            cmp = parseFloat(a.price) - parseFloat(b.price);
        } else if (sortBy.value === 'name') {
            cmp = a.name.localeCompare(b.name);
        } else if (sortBy.value === 'date') {
            const dateA = a.created_at ? new Date(a.created_at).getTime() : a.id;
            const dateB = b.created_at ? new Date(b.created_at).getTime() : b.id;
            cmp = dateA - dateB;
        }
        
        return sortOrder.value === 'desc' ? -cmp : cmp;
    });

    return result;
});

onMounted(() => {
    document.addEventListener('click', closeFilterMenu);
    productStore.fetchProducts();
    productStore.fetchCategories();
});

onUnmounted(() => {
    document.removeEventListener('click', closeFilterMenu);
});
</script>

<style scoped>
.products-page {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.products-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.products-header {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.header-title-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.header-title-bar h1 {
    margin: 0;
    font-size: 2rem;
}

.products-controls-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.categories-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.cat-chip {
    padding: 0.5rem 1.25rem;
    border-radius: 20px;
    border: 1px solid var(--border);
    background-color: var(--surface);
    color: var(--text);
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.cat-chip:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.cat-chip.active {
    background-color: var(--primary);
    border-color: var(--primary);
    color: #FFF;
}

.filter-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background-color: var(--surface);
    color: var(--text);
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.action-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.sort-order-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.25rem 0.5rem 0.25rem 1rem;
}

.sort-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

.sort-select {
    border: none;
    background: transparent;
    color: var(--text);
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    outline: none;
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
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.loading, .empty {
    text-align: center;
    padding: 5rem;
    font-size: 1.2rem;
    color: #888;
}

.filter-dropdown-wrapper {
    position: relative;
}

.filter-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 250px;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    padding: 1rem;
    z-index: 100;
}

.filter-dropdown-header {
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
}

.filter-dropdown-header h3 {
    margin: 0;
    font-size: 1rem;
    color: var(--text);
}

.filter-dropdown-body {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text);
}

.filter-input {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    background-color: var(--background);
    color: var(--text);
    outline: none;
    font-size: 0.9rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: normal !important;
    cursor: pointer;
    font-size: 0.9rem;
}

[data-theme='dark'] .cat-chip {
    color: #DDD;
}
</style>
