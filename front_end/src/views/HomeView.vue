<template>
  <div class="home-view">
    <header class="home-header">
      <div class="home-header__content">
        <h1>Bienvenue sur E-Shop</h1>
        <p>Découvrez nos produits exceptionnels pour tous vos besoins.</p>
        <router-link :to="productsUrl" class="btn-primary">Voir tous les produits</router-link>
      </div>
    </header>

    <section class="section">
        <h2 class="section__title">🔥 Nouveautés</h2>
        <div v-if="productStore.loading" class="loading">
            <i class='bx bx-loader-alt bx-spin'></i> Chargement...
        </div>
        <div v-else class="product-grid">
            <ProductCard 
                v-for="product in productStore.products.slice(0, 4)" 
                :key="product.id" 
                :product="product"
            />
        </div>
        <div v-if="!productStore.loading && productStore.products.length === 0" class="empty">
            Aucun produit disponible pour le moment.
        </div>
    </section>

    <section class="section section--light">
        <div class="features">
            <div class="feature-item">
                <i class='bx bx-truck'></i>
                <h3>Livraison Rapide</h3>
                <p>Recevez vos commandes en un temps record.</p>
            </div>
            <div class="feature-item">
                <i class='bx bx-shield-quarter'></i>
                <h3>Paiement Sécurisé</h3>
                <p>Vos transactions sont protégées à 100%.</p>
            </div>
            <div class="feature-item">
                <i class='bx bx-support'></i>
                <h3>Support 24/7</h3>
                <p>Notre équipe est là pour vous aider à tout moment.</p>
            </div>
        </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useProductStore } from '@/stores/products';
import { useAuthStore } from '@/stores/auth';
import ProductCard from '@/components/ProductCard.vue';

const productStore = useProductStore();
const authStore = useAuthStore();

const productsUrl = computed(() => {
    if (authStore.isAuthenticated) {
        if (authStore.isAdmin) return '/admin/products';
        if (authStore.isLivreur) return '/livreur/products';
        if (authStore.isUser) return '/client/products';
    }
    return '/products';
});

onMounted(() => {
    productStore.fetchProducts();
});
</script>

<style scoped>
.home-view {
  display: flex;
  flex-direction: column;
  gap: 4rem;
  padding-bottom: 4rem;
}

.home-header {
  background: linear-gradient(135deg, var(--primary) 0%, #2a2a4e 100%);
  color: #FFFFFF;
  padding: 6rem 2rem;
  border-radius: 20px;
  text-align: center;
  margin: 0 1rem;
}

.home-header__content {
    max-width: 800px;
    margin: 0 auto;
}

.home-header h1 {
  font-size: 3.5rem;
  margin: 0;
  font-weight: 800;
}

.home-header p {
  font-size: 1.25rem;
  opacity: 0.9;
  margin: 1.5rem 0 2.5rem;
}

.btn-primary {
    background-color: var(--secondary);
    color: #FFFFFF;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.1rem;
    transition: transform 0.2s;
    display: inline-block;
}

.section {
    padding: 0 2.5rem;
}

.section__title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: var(--primary);
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2.5rem;
}

.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 3rem;
    padding: 4rem 2rem;
    background-color: var(--neutral);
    border-radius: 20px;
}

.feature-item {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.feature-item i {
    font-size: 3rem;
    color: var(--secondary);
}

.feature-item h3 {
    font-size: 1.25rem;
    margin: 0;
    font-weight: 700;
}

.feature-item p {
    color: #666;
    margin: 0;
}

.loading, .empty {
    text-align: center;
    padding: 3rem;
    font-size: 1.2rem;
    color: #888;
}

[data-theme='dark'] .section__title {
    color: var(--secondary);
}

[data-theme='dark'] .feature-item p {
    color: #AAA;
}
</style>
