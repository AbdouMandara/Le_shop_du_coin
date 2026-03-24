<template>
  <div class="products-view-container">
    <div v-if="authStore.isUser" class="products-page">
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


    <!-- Coté admin  -->
    

    <div v-if="authStore.isAdmin" class="admin-content">
        <div class="content-header">
            <h3>Gestion des Produits</h3>
            <button class="btn-add" @click="openProductModal()">Ajouter un produit</button>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="p in productStore.products" :key="p.id">
                    <td>{{ p.name }}</td>
                    <td>{{ p.price }} FCFA</td>
                    <td>{{ p.quantity }}</td>
                    <td class="actions">
                        <button class="btn-icon" @click="openProductModal(p)"><i class='bx bx-edit'></i></button>
                        <button class="btn-icon delete" @click="deleteProduct(p.id)"><i class='bx bx-trash'></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal pour ajouter/modifier produit -->
    <div v-if="showProductModal" class="modal-overlay" @click.self="closeProductModal">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ editingProduct ? 'Modifier le produit' : 'Ajouter un produit' }}</h2>
                <button class="btn-close" @click="closeProductModal">&times;</button>
            </div>
            <form @submit.prevent="saveProduct" class="modal-body">
                <div class="form-group">
                    <label>Nom du produit</label>
                    <input type="text" v-model="productForm.name" placeholder="ex: Smartphone Pro" required>
                </div>
                <div class="form-group">
                    <label>Prix (FCFA)</label>
                    <input type="number" v-model="productForm.price" step="0.01" placeholder="ex: 150000" required>
                </div>
                <div class="form-group">
                    <label>Quantité en stock</label>
                    <input type="number" v-model="productForm.quantity" placeholder="ex: 50" required>
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select v-model="productForm.category_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option v-for="cat in productStore.categories" :key="cat.id" :value="cat.id">{{ cat.label }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea v-model="productForm.description" placeholder="Description du produit..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" @click="closeProductModal">Annuler</button>
                    <button type="submit" class="btn-primary">{{ editingProduct ? 'Modifier' : 'Ajouter' }}</button>
                </div>
            </form>
        </div>
    </div>

    <div v-if="tab === 'orders'" class="admin-content">
        <h3>Toutes les Commandes</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Produit</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="o in orderStore.orders" :key="o.id">
                    <td>{{ o.user?.name }}</td>
                    <td>{{ o.product?.name }}</td>
                    <td><span class="status-badge" :class="o.status">{{ o.status }}</span></td>
                    <td>
                        <select v-model="o.status" @change="orderStore.updateOrderStatus(o.id, o.status)">
                            <option value="pending">En attente</option>
                            <option value="paid">Payé</option>
                            <option value="delivered">Livré</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useProductStore } from '@/stores/products';
import { useOrderStore } from '@/stores/orders';
import ProductCard from '@/components/ProductCard.vue';
import {useAuthStore}  from '@/stores/auth'
// coté admin

const productStore = useProductStore();
const authStore = useAuthStore()
const orderStore = useOrderStore();
const tab = ref('products');

const showProductModal = ref(false);

// Empêcher le scroll du body quand le modal est ouvert
watch(showProductModal, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});
const editingProduct = ref(null);
const productForm = ref({
    name: '',
    price: '',
    quantity: '',
    category_id: '',
    description: ''
});

const openProductModal = (product = null) => {
    editingProduct.value = product;
    if (product) {
        productForm.value = { ...product };
    } else {
        productForm.value = {
            name: '',
            price: '',
            quantity: '',
            category_id: '',
            description: ''
        };
    }
    showProductModal.value = true;
};

const closeProductModal = () => {
    showProductModal.value = false;
    editingProduct.value = null;
};

const saveProduct = async () => {
    try {
        if (editingProduct.value) {
            await productStore.updateProduct(editingProduct.value.id, productForm.value);
        } else {
            await productStore.addProduct(productForm.value);
        }
        closeProductModal();
    } catch (error) {
        console.error('Erreur lors de la sauvegarde du produit:', error);
        // Afficher un message d'erreur si nécessaire
    }
};

const deleteProduct = async (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
        try {
            await productStore.deleteProduct(id);
        } catch (error) {
            console.error('Erreur lors de la suppression du produit:', error);
        }
    }
};

onMounted(() => {
    productStore.fetchProducts();
    productStore.fetchCategories();
    orderStore.fetchOrders();
});



// Coté client
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
/* Coté admin */

.admin-page {
    padding: 2rem;
}

.admin-tabs {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.admin-tabs button {
    background: none;
    border: none;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
    font-weight: 600;
    color: #888;
    border-bottom: 3px solid transparent;
}

.admin-tabs button.active {
    color: var(--primary);
    border-bottom-color: var(--secondary);
}

.admin-content {
    padding: 1.5em;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.btn-add {
    background-color: var(--secondary);
    color: #FFFFFF;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--surface);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border);
}

.admin-table th, .admin-table td {
    padding: 1rem 1.5rem;
    text-align: left;
}

.admin-table th {
    background-color: var(--neutral);
    font-weight: 700;
}

.admin-table tr:not(:last-child) {
    border-bottom: 1px solid var(--border);
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--primary);
}

.btn-icon.delete {
    color: #ff4d4d;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.pending { background: #FFF3E0; color: #E65100; }
.status-badge.paid { background: #E8F5E9; color: #2E7D32; }

select {
    padding: 0.4rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background-color: var(--background);
    color: var(--text);
}

/* Modal styles aligné avec AdminLivreurs */
.modal-overlay {
    position: fixed;
    top: 55px;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: flex-start; /* Aligne en haut */
    padding-top: 5vh; /* Espace du haut */
    z-index: 1000;
}

.modal {
    background: var(--surface);
    width: 100%;
    max-width: 500px;
    max-height: 90vh; /* Empêche le modal de dépasser l'écran */
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 { margin: 0; font-size: 1.3rem; }

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text);
}

.modal-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    overflow-y: auto; /* Permet le scroll interne */
    flex: 1;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text);
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background-color: var(--background);
    color: var(--text);
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1rem;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-secondary {
    background: var(--neutral);
    color: var(--text);
    border: 1px solid var(--border);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}

</style>
