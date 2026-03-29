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
      <div class="products-section-container">
        <div class="section-header">
          <h2 class="section-title">En Tendance</h2>
          <router-link :to="productsUrl" class="link-more">Voir toute la boutique <i class='bx bx-right-arrow-alt'></i></router-link>
        </div>
        
        <div v-if="productStore.loading" class="loading-state">
           <i class='bx bx-loader-alt bx-spin'></i> Chargement de nos collections...
        </div>
        <div v-else class="carousel-container">
           <div class="carousel-viewport">
              <div class="carousel-track" :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
                 <div class="carousel-page" v-for="pageIndex in slideCount" :key="pageIndex">
                    <ProductCard 
                       v-for="product in featuredProducts.slice((pageIndex - 1) * itemsPerPage, pageIndex * itemsPerPage)" 
                       :key="product.id" 
                       :product="product"
                    />
                 </div>
              </div>
           </div>
           <div class="carousel-controls" v-if="slideCount > 1">
              <button @click="prevSlide" :disabled="currentSlide === 0" class="carousel-btn"><i class='bx bx-chevron-left'></i></button>
              <div class="carousel-dots">
                 <span v-for="n in slideCount" :key="n" class="dot" :class="{ active: currentSlide === n - 1 }" @click="goToSlide(n - 1)"></span>
              </div>
              <button @click="nextSlide" :disabled="currentSlide >= slideCount - 1" class="carousel-btn"><i class='bx bx-chevron-right'></i></button>
           </div>
        </div>
        <div v-if="!productStore.loading && productStore.products.length === 0" class="empty-state">
           Notre collection est en cours de renouvellement. Revenez très vite !
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
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

// Carousel logic
const itemsPerPage = ref(4);
const currentSlide = ref(0);

const updateItemsPerPage = () => {
    if (window.innerWidth < 768) itemsPerPage.value = 1;
    else if (window.innerWidth < 1024) itemsPerPage.value = 2;
    else if (window.innerWidth < 1400) itemsPerPage.value = 3;
    else itemsPerPage.value = 4;
};

watch(itemsPerPage, () => {
    currentSlide.value = 0;
});

const featuredProducts = computed(() => {
    return productStore.products.slice(0, 6) || [];
});

const slideCount = computed(() => {
    if (featuredProducts.value.length === 0) return 0;
    return Math.ceil(featuredProducts.value.length / itemsPerPage.value);
});

const prevSlide = () => {
    if (currentSlide.value > 0) currentSlide.value--;
};

const nextSlide = () => {
    if (currentSlide.value < slideCount.value - 1) currentSlide.value++;
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

onMounted(() => {
    productStore.fetchProducts();
    updateItemsPerPage();
    window.addEventListener('resize', updateItemsPerPage);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateItemsPerPage);
});
</script>

<style scoped>
.home-view {
  display: flex;
  flex-direction: column;
  gap: 5rem;
  padding-bottom: 6rem;
}

/* HERO SECTION */
.hero-section {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 8rem 2rem;
  background-color: var(--primary);
  min-height: calc(100vh - 60px);
  width: 100%;
}

.features-section {
  padding: 0 2.5rem;
  max-width: 1300px;
  width: 100%;
  margin: 0 auto;
}

.products-section {
  background-color: var(--primary);
  padding: 6rem 2.5rem;
  width: 100%;
  position: relative;
}

.products-section-container {
  max-width: 1300px;
  margin: 0 auto;
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
  color: #FFFFFF;
  margin-bottom: 2rem;
  letter-spacing: -0.04em;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: #EEEEEE;
  margin-bottom: 3rem;
  line-height: 1.7;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

[data-theme='dark'] .hero-subtitle {
  color: #DDDDDD;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-cta {
  background-color: var(--secondary);
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
  color: #FFFFFF;
  margin: 0;
  letter-spacing: -0.03em;
}

.link-more {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #FFFFFF;
  font-weight: 600;
  font-size: 1.05rem;
  transition: gap 0.2s, opacity 0.2s;
  padding-bottom: 0.5rem;
  opacity: 0.9;
}

.link-more:hover {
  gap: 0.85rem;
  opacity: 0.8;
}

/* CAROUSEL */
.carousel-container {
  display: flex;
  flex-direction: column;
  width: 100%;
}
.carousel-viewport {
  overflow: hidden;
  width: 100%;
}
.carousel-track {
  display: flex;
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
}
.carousel-page {
  flex: 0 0 100%;
  width: 100%;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2.5rem;
  padding: 0.5rem 0; /* Ensures box shadows aren't clipped */
}
.carousel-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 2rem;
}
.carousel-btn {
  background-color: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.4);
  color: #FFFFFF;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  backdrop-filter: blur(4px);
}
.carousel-btn:hover:not(:disabled) {
  background-color: #FFFFFF;
  color: var(--secondary);
  border-color: #FFFFFF;
}
.carousel-btn:disabled {
  opacity: 0.2;
  cursor: not-allowed;
}
.carousel-dots {
  display: flex;
  gap: 0.6rem;
}
.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: all 0.2s ease;
}
.dot.active {
  background-color: #FFFFFF;
  transform: scale(1.3);
}

/* Glassmorphism Product Card Override for this section */
:deep(.products-section .product-card) {
  background-color: rgba(255, 255, 255, 0.95);
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

:deep(.products-section .product-card:hover) {
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

:deep(.products-section .product-card__content) {
  background-color: transparent;
}

@media (max-width: 1400px) {
  .carousel-page { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 1024px) {
  .carousel-page { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .carousel-page { grid-template-columns: repeat(1, 1fr); }
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
