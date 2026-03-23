<template>
  <div class="home-view">
    <!-- HERO SECTION -->
    <section class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title">L'art de l'exceptionnel, au quotidien.</h1>
        <p class="hero-subtitle">Découvrez notre sélection de produits haut de gamme conçus pour sublimer votre style de vie et répondre à toutes vos exigences.</p>
        <div class="hero-actions">
          <router-link :to="productsUrl" class="btn-cta">Explorer la collection <i class='bx bx-right-arrow-alt'></i></router-link>
        </div>
      </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features-section">
      <div class="feature-card">
        <div class="feature-icon"><i class='bx bx-rocket'></i></div>
        <div class="feature-text">
          <h3>Livraison Express</h3>
          <p>Recevez vos commandes en 24h chrono.</p>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class='bx bx-check-shield'></i></div>
        <div class="feature-text">
          <h3>Paiement Sécurisé</h3>
          <p>Vos transactions sont 100% chiffrées.</p>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class='bx bx-headphone'></i></div>
        <div class="feature-text">
          <h3>Support Premium</h3>
          <p>Une équipe dédiée à votre écoute 7j/7.</p>
        </div>
      </div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section class="products-section">
      <div class="section-header">
        <h2 class="section-title">En Tendance</h2>
        <router-link :to="productsUrl" class="link-more">Voir toute la boutique <i class='bx bx-right-arrow-alt'></i></router-link>
      </div>
      
      <div v-if="productStore.loading" class="loading-state">
         <i class='bx bx-loader-alt bx-spin'></i> Chargement de nos collections...
      </div>
      <div v-else class="products-grid">
         <ProductCard 
            v-for="product in productStore.products.slice(0, 4)" 
            :key="product.id" 
            :product="product"
         />
      </div>
      <div v-if="!productStore.loading && productStore.products.length === 0" class="empty-state">
         Notre collection est en cours de renouvellement. Revenez très vite !
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
  padding: 2rem 2.5rem 6rem;
  max-width: 1300px;
  margin: 0 auto;
}

/* HERO SECTION */
.hero-section {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 8rem 2rem;
  background-color: var(--neutral);
  border-radius: 32px;
  border: 1px solid var(--border);
  position: relative;
  overflow: hidden;
}

.hero-content {
  max-width: 850px;
  position: relative;
  z-index: 2;
}

.hero-title {
  font-size: 4.5rem;
  font-weight: 800;
  line-height: 1.1;
  color: var(--text);
  margin-bottom: 2rem;
  letter-spacing: -0.04em;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: #666;
  margin-bottom: 3rem;
  line-height: 1.7;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

[data-theme='dark'] .hero-subtitle {
  color: #AAA;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-cta {
  background-color: var(--primary);
  color: #FFFFFF;
  padding: 1.1rem 2.5rem;
  border-radius: 50px;
  font-weight: 600;
  font-size: 1.1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.btn-cta i {
  font-size: 1.3rem;
  transition: transform 0.3s ease;
}

.btn-cta:hover {
  box-shadow: 0 12px 32px rgba(0,0,0,0.2);
}

.btn-cta:hover i {
  transform: translateX(4px);
}

/* FEATURES SECTION */
.features-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 2rem;
}

.feature-card {
  background-color: var(--background);
  border: 1px solid var(--border);
  padding: 2.5rem 2rem;
  border-radius: 24px;
  display: flex;
  gap: 1.5rem;
  align-items: center;
  transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease, border-color 0.3s ease;
}



.feature-icon {
  width: 70px;
  height: 70px;
  background-color: var(--neutral);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.2rem;
  color: var(--primary);
  flex-shrink: 0;
  transition: background-color 0.3s, color 0.3s;
}


.feature-text h3 {
  margin: 0 0 0.5rem;
  font-size: 1.25rem;
  color: var(--text);
  font-weight: 700;
}

.feature-text p {
  margin: 0;
  color: #888;
  font-size: 0.95rem;
  line-height: 1.5;
}

/* PRODUCTS SECTION */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 3rem;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--text);
  margin: 0;
  letter-spacing: -0.03em;
}

.link-more {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--primary);
  font-weight: 600;
  font-size: 1.05rem;
  transition: gap 0.2s, opacity 0.2s;
  padding-bottom: 0.5rem;
}

.link-more:hover {
  gap: 0.85rem;
  opacity: 0.8;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2.5rem;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 6rem;
  color: #888;
  background-color: var(--neutral);
  border-radius: 24px;
  border: 1px dashed var(--border);
  font-size: 1.2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.loading-state i {
  font-size: 3rem;
  color: var(--primary);
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 3rem;
  }
  .hero-section {
    padding: 5rem 1.5rem;
  }
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
</style>
