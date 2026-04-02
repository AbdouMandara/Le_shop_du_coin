<template>
  <div class="home-view">
    <!-- HERO SECTION (Promotional Carousel) -->
    <section class="hero-section" :class="{ 'has-carousel': productStore.promotionalProducts.length > 0 }">
      <div v-if="productStore.loadingPromotions" class="hero-loader">
        <div class="loader-ripple"><div></div><div></div></div>
      </div>
      
      <div v-else-if="productStore.promotionalProducts.length > 0" class="hero-container container">
        <div class="hero-header-content">
          <h1 class="hero-title stagger-item">Nos produits en promotion</h1>
        </div>

        <div class="hero-card-carousel">
          <div class="hero-carousel__viewport-container">
            <button 
              @click="prevHeroSlide" 
              class="hero-side-btn prev" 
              v-if="totalSlides > 1"
            >
              <i class='bx bx-chevron-left'></i>
            </button>

            <div class="hero-carousel__viewport">
              <div 
                class="hero-carousel__track" 
                :style="{ transform: `translateX(-${currentHeroSlide * slideWidth}%)` }"
              >
                <!-- Original Items -->
                <div 
                  v-for="(product, index) in productStore.promotionalProducts" 
                  :key="product.id" 
                  class="hero-card-slide"
                  :class="{ 'is-middle': isMiddle(index) }"
                >
                  <div class="stagger-card" :style="{ animationDelay: `${(index % 3) * 0.15}s` }">
                    <ProductCard :product="product" />
                  </div>
                </div>
                <!-- Clones for smooth wrap-around appearance -->
                <div 
                  v-for="(product, index) in productStore.promotionalProducts.slice(0, 2)" 
                  :key="'clone-' + product.id" 
                  class="hero-card-slide"
                  :class="{ 'is-middle': isMiddle(productStore.promotionalProducts.length + index) }"
                >
                  <div class="stagger-card">
                    <ProductCard :product="product" />
                  </div>
                </div>
              </div>
            </div>

            <button 
              @click="nextHeroSlide" 
              class="hero-side-btn next" 
              v-if="totalSlides > 1"
            >
              <i class='bx bx-chevron-right'></i>
            </button>
          </div>

          <!-- Pagination Dots below -->
          <div class="hero-carousel__dots" v-if="totalSlides > 1">
            <span 
              v-for="i in totalSlides" 
              :key="i" 
              class="hero-dot" 
              :class="{ active: currentHeroSlide === i - 1 }"
              @click="goToHeroSlide(i - 1)"
            ></span>
          </div>
        </div>
        
        <!-- Timer bar centered at bottom of hero -->
        <div class="hero-timer-container">
           <div class="hero-timer-bar" :style="{ width: carouselProgress + '%' }"></div>
        </div>
      </div>

      <!-- Fallback Hero -->
      <div v-else class="hero-content-fallback">
        <div class="hero-fallback-text">
          <h1>L'excellence à votre portée.</h1>
          <p>Découvrez une sélection exclusive de produits pensés pour votre quotidien.</p>
          <button @click="scrollToDiscovery" class="btn-cta-large">Explorer la Boutique</button>
        </div>
      </div>
    </section>

    <!-- VALUE PROPOSITION (Plus-value) -->
    <section class="usp-section">
      <div class="container">
        <div class="usp-grid">
          <div class="usp-item">
            <div class="usp-icon-wrapper"><i class='bx bx-rocket'></i></div>
            <div class="usp-content">
              <h3>Livraison Express</h3>
              <p>Partout au Cameroun en 24/48h.</p>
            </div>
          </div>
          <div class="usp-item">
            <div class="usp-icon-wrapper"><i class='bx bx-shield-quarter'></i></div>
            <div class="usp-content">
              <h3>Sécurité Garantie</h3>
              <p>Paiements mobiles 100% sécurisés.</p>
            </div>
          </div>
          <div class="usp-item">
            <div class="usp-icon-wrapper"><i class='bx bx-support'></i></div>
            <div class="usp-content">
              <h3>Support 24/7</h3>
              <p>Conseillers à votre écoute au quotidien.</p>
            </div>
          </div>
          
        </div>
      </div>
    </section>

    <!-- PRODUCT DISCOVERY SECTION -->
    <section id="discovery" class="discovery-section">
      <div class="container">
        <div class="section-header-modern">
          <div class="header-left">
            <h2 class="title-sub">Découvrez notre collection</h2>
            <h1 class="title-main">Produits pour vous</h1>
          </div>
        </div>

        <div class="filter-tabs-container">
          <div class="filter-tabs">
            <button 
              class="tab-btn" 
              :class="{ active: activeCategory === 'all' }"
              @click="activeCategory = 'all'"
            >
              Tous
            </button>
            <button 
              v-for="cat in productStore.categories" 
              :key="cat.id" 
              class="tab-btn"
              :class="{ active: activeCategory === cat.id }"
              @click="activeCategory = cat.id"
            >
              {{ cat.label }}
            </button>
          </div>
        </div>

        <div v-if="productStore.loading" class="loading-grid">
           <div class="grid-spinner"></div>
           <p>Chargement des merveilles...</p>
        </div>
        
        <div v-else-if="filteredProducts.length > 0" class="products-grid-modern">
           <TransitionGroup name="grid">
             <ProductCard 
                v-for="product in filteredProducts.slice(0, visibleCount)" 
                :key="product.id" 
                :product="product"
                class="modern-card-item"
             />
           </TransitionGroup>
        </div>

        <div v-else-if="!productStore.loading" class="empty-results">
           <i class='bx bx-ghost'></i>
           <h3>Aucun produit ne correspond à votre recherche</h3>
           <p>Essayez avec d'autres termes ou catégories.</p>
           <button @click="resetFilters" class="btn-text">Réinitialiser les filtres</button>
        </div>

        <div v-if="hasMoreProducts" class="load-more-container">
           <button @click="loadMore" class="btn-load-more">
             Voir plus de produits <span>({{ filteredProducts.length - visibleCount }} restants)</span>
           </button>
        </div>
      </div>
    </section>

    <!-- PROMO MODAL -->
    <Teleport to="body">
      <div v-if="showPromoModal" class="promo-modal-overlay" @click.self="closePromoModal">
        <div class="promo-modal-container">
          <button class="modal-close-btn" @click="closePromoModal"><i class='bx bx-x'></i></button>
          <div v-if="selectedPromoProduct" class="promo-modal-content">
            <div class="modal-image-side">
              <img :src="getProductImage(selectedPromoProduct.image)" :alt="selectedPromoProduct.name" />
              <div class="promo-badge-modal">PROMO -{{ calculateDiscount() }}%</div>
            </div>
            <div class="modal-info-side">
              <h2 class="modal-title">{{ selectedPromoProduct.name }}</h2>
              <div class="modal-pricing">
                <span class="m-price-old">{{ formatPrice(selectedPromoProduct.original_price) }} FCFA</span>
                <span class="m-price-new">{{ formatPrice(selectedPromoProduct.price) }} FCFA</span>
              </div>
              <p class="modal-desc">{{ selectedPromoProduct.description }}</p>
              
              <div class="modal-actions">
                <button @click="addToCart(selectedPromoProduct)" class="btn-modal-primary">
                  <i class='bx bx-cart-add'></i> Ajouter au panier
                </button>
                <router-link :to="`/product/${selectedPromoProduct.id}`" class="btn-modal-link">
                  Détails complets
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useProductStore } from '@/stores/products';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import ProductCard from '@/components/ProductCard.vue';

const productStore = useProductStore();
const authStore = useAuthStore();
const cartStore = useCartStore();

// Navigation logic based on role
const productsUrl = computed(() => {
    if (authStore.isAuthenticated) {
        if (authStore.isAdmin) return '/admin/products';
        if (authStore.isLivreur) return '/livreur/products';
        if (authStore.isUser) return '/client/products';
    }
    return '/products';
});

// --- HERO CAROUSEL LOGIC ---
const currentHeroSlide = ref(0);
const carouselProgress = ref(0);
const itemsPerPage = ref(3);
const slideWidth = computed(() => 100 / itemsPerPage.value); // Move by 1 item (33.33%)

const totalSlides = computed(() => {
    return productStore.promotionalProducts.length;
});

const isMiddle = (index) => {
    const len = productStore.promotionalProducts.length;
    if (len === 0) return false;
    // We want the item at (currentHeroSlide + 1) to be scaled.
    // This applies both to original items and their clones at the end.
    return (index % len) === (currentHeroSlide.value + 1) % len;
};


let heroInterval;
let progressInterval;
const SLIDE_DURATION = 6000;

const nextHeroSlide = () => {
    const len = productStore.promotionalProducts.length;
    if (len === 0) return;
    currentHeroSlide.value = (currentHeroSlide.value + 1) % len;
    carouselProgress.value = 0;
};

const prevHeroSlide = () => {
    const len = productStore.promotionalProducts.length;
    if (len === 0) return;
    currentHeroSlide.value = (currentHeroSlide.value - 1 + len) % len;
    carouselProgress.value = 0;
};

const goToHeroSlide = (index) => {
    currentHeroSlide.value = index;
    carouselProgress.value = 0;
    startHeroAutoPlay(); 
};

const startHeroAutoPlay = () => {
    stopHeroAutoPlay();
    heroInterval = setInterval(nextHeroSlide, SLIDE_DURATION);
    
    let startTime = Date.now();
    progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        carouselProgress.value = Math.min((elapsed / SLIDE_DURATION) * 100, 100);
        if (carouselProgress.value >= 100) {
            startTime = Date.now(); // reset for next slide
        }
    }, 30);
};

const stopHeroAutoPlay = () => {
    if (heroInterval) clearInterval(heroInterval);
    if (progressInterval) clearInterval(progressInterval);
};

// --- PRODUCT DISCOVERY LOGIC ---
const activeCategory = ref('all');
const visibleCount = ref(10);

const filteredProducts = computed(() => {
  return productStore.products.filter(product => {
    const query = productStore.searchQuery.toLowerCase();
    const matchesSearch = product.name.toLowerCase().includes(query) || 
                          product.description?.toLowerCase().includes(query);
    const matchesCategory = activeCategory.value === 'all' || product.category_id == activeCategory.value;
    return matchesSearch && matchesCategory;
  });
});

const hasMoreProducts = computed(() => {
  return visibleCount.value < filteredProducts.value.length;
});

const loadMore = () => {
  visibleCount.value += 10;
};

const resetFilters = () => {
  productStore.searchQuery = '';
  activeCategory.value = 'all';
};

const scrollToDiscovery = () => {
  document.getElementById('discovery')?.scrollIntoView({ behavior: 'smooth' });
};

// --- PROMO MODAL LOGIC ---
const showPromoModal = ref(false);
const selectedPromoProduct = ref(null);

const openPromoModal = (product) => {
  selectedPromoProduct.value = product;
  showPromoModal.value = true;
  document.body.style.overflow = 'hidden';
};

const closePromoModal = () => {
  showPromoModal.value = false;
  document.body.style.overflow = '';
};

const calculateDiscount = () => {
  if (!selectedPromoProduct.value) return 0;
  const p = selectedPromoProduct.value;
  if (!p.original_price || p.original_price <= p.price) return 0;
  return Math.round(((p.original_price - p.price) / p.original_price) * 100);
};

const addToCart = (product) => {
  cartStore.addToCart(product);
  closePromoModal();
  // Optional: show a notification
};

// --- UTILITIES ---
const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};

const getProductImage = (path) => {
    if (!path) return 'https://placehold.co/1200x600?text=Produit';
    if (path.startsWith('http')) return path;
    return `/storage/${path}`;
};

const handleImgError = (e) => {
  e.target.src = 'https://placehold.co/1200x600?text=Image+Indisponible';
};

// Lifecycle
onMounted(async () => {
    await Promise.all([
        productStore.fetchProducts(),
        productStore.fetchPromotionalProducts(),
        productStore.fetchCategories()
    ]);
    startHeroAutoPlay();
});

onUnmounted(() => {
    stopHeroAutoPlay();
});


// Watchers
watch(activeCategory, () => {
  visibleCount.value = 10;
});
</script>

<style scoped>
.home-view {
  display: flex;
  flex-direction: column;
  gap: 0; /* Removing large gaps to handle section spacing internally */
  background-color: var(--background);
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  width: 100%;
}

/* --- HERO SECTION --- */
.hero-section {
  position: relative;
  height: 90vh;
  min-height: 600px;
  overflow: hidden;
  background-color: #000;
}

.hero-loader {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: white;
}

.loader-ripple {
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}
.loader-ripple div {
  position: absolute;
  border: 4px solid var(--secondary);
  opacity: 1;
  border-radius: 50%;
  animation: ripple 1s cubic-bezier(0, 0.2, 0.8, 1) infinite;
}
.loader-ripple div:nth-child(2) {
  animation-delay: -0.5s;
}
@keyframes ripple {
  0% { top: 36px; left: 36px; width: 0; height: 0; opacity: 0; }
  4.9% { top: 36px; left: 36px; width: 0; height: 0; opacity: 0; }
  5% { top: 36px; left: 36px; width: 0; height: 0; opacity: 1; }
  100% { top: 0px; left: 0px; width: 72px; height: 72px; opacity: 0; }
}

.hero-carousel {
  height: 100%;
  width: 100%;
  position: relative;
}

.hero-carousel__viewport {
  height: 100%;
  width: 100%;
  overflow: hidden;
}

.hero-carousel__track {
  display: flex;
  height: 100%;
  transition: transform 0.8s cubic-bezier(0.65, 0, 0.35, 1);
}

.hero-slide {
  flex: 0 0 100%;
  height: 100%;
  position: relative;
  display: flex;
  align-items: center;
  padding: 0 5%;
}

.hero-slide__image {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  z-index: 1;
}

.hero-slide__image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-slide__overlay {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0) 100%);
  z-index: 2;
}
/* --- HERO SECTION (NEW CARD CAROUSEL) --- */
.hero-section {
background-color: var(--primary);
    min-height: 95lvh;
    display: flex;
    align-items: center;
    overflow: hidden;
    color: white;
}

.hero-header-content {
  text-align: center;
  max-width: 900px;
  margin-left: auto;
  margin-right: auto;
  position: relative;
  z-index: 10;
}

.hero-title {
  font-size: clamp(3rem, 6vw, 4.5rem);
  font-weight: 800;
  margin-bottom: 2rem;
  line-height: 1.05;
  letter-spacing: -0.03em;
  text-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.hero-lead {
  font-size: 1.3rem;
  color: rgba(255, 255, 255, 0.9);
  max-width: 650px;
  margin: 0 auto;
  line-height: 1.6;
}

/* Animations Staggered */
.stagger-item {
  opacity: 0;
  transform: translateY(40px);
  animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.hero-header-content .stagger-item:nth-child(1) { animation-delay: 0.1s; }
.hero-header-content .stagger-item:nth-child(2) { animation-delay: 0.3s; }
.hero-header-content .stagger-item:nth-child(3) { animation-delay: 0.5s; }

@keyframes fadeInUp {
  to { opacity: 1; transform: translateY(0); }
}

/* Carousel Track Layout */
.hero-card-carousel {
  width: 100%;
  padding: 0 1rem;
}

.hero-carousel__viewport-container {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  position: relative;
  width: 100%;
}

.hero-carousel__viewport {
  flex: 1;
  overflow: hidden; /* Back to hidden for proper side clipping */
  padding: 2rem 0 3rem;
}

.hero-carousel__track {
  display: flex;
  align-items: center;
  transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.hero-card-slide {
  flex: 0 0 calc(100% / 3);
  padding: 0 20px;
  display: flex;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.hero-card-slide.is-middle {
  transform: scale(1.15);
  z-index: 10;
}

.hero-card-slide :deep(.product-card) {
  flex: 1;
  height: 100%;
  width: 100%;
  box-shadow: 0 15px 35px rgba(0,0,0,0.1);
  transition: all 0.5s ease;
  background-color: white;
}

.hero-card-slide.is-middle :deep(.product-card) {
  box-shadow: 0 25px 60px rgba(0,0,0,0.2);
  border: 1px solid var(--secondary);
}

/* Side Buttons */
.hero-side-btn {
  background: white;
  border: none;
  color: var(--primary);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  z-index: 50;
  flex-shrink: 0;
}

.hero-side-btn:hover:not(:disabled) {
  transform: scale(1.1);
  background-color: var(--secondary);
  color: white;
}

.hero-side-btn:disabled {
  opacity: 0.2;
  cursor: not-allowed;
}

.hero-carousel__dots {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 1rem;
}

.hero-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: all 0.4s ease;
}

.hero-dot.active {
  background: var(--secondary);
  transform: scale(1.3);
}

.hero-carousel__nav {
  position: absolute;
  bottom: 50px; right: 5%;
  display: flex;
  align-items: center;
  gap: 2rem;
  z-index: 20;
}

.nav-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  width: 50px; height: 50px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(5px);
}

.nav-btn:hover {
  background: white;
  color: black;
}

.hero-carousel__indicators {
  display: flex;
  gap: 0.75rem;
}

.indicator-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: all 0.3s ease;
}

.indicator-dot.active {
  width: 30px;
  border-radius: 4px;
  background: var(--secondary);
}

/* Fallback Hero */
.hero-content-fallback {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  background: var(--primary);
  color: white;
}

.hero-fallback-text h1 { font-size: 4rem; margin-bottom: 1.5rem; }
.hero-fallback-text p { font-size: 1.25rem; margin-bottom: 3rem; opacity: 0.8; }

.btn-cta-large {
  background: var(--secondary);
  color: white;
  padding: 1.5rem 3.5rem;
  border-radius: 50px;
  font-size: 1.25rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: transform 0.3s ease;
}
.btn-cta-large:hover { transform: scale(1.05); }

/* --- USP SECTION --- */
.usp-section {
  padding: 6rem 0;
  background-color: var(--neutral);
}

.usp-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 3rem;
}

.usp-item {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.usp-icon-wrapper {
  width: 64px; height: 64px;
  background: white;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: var(--primary);
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

.usp-content h3 { font-size: 1.1rem; margin-bottom: 0.25rem; font-weight: 700; }
.usp-content p { font-size: 0.9rem; color: #666; margin: 0; }

/* --- DISCOVERY SECTION --- */
.discovery-section {
  padding: 8rem 0;
}

.section-header-modern {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 4rem;
}

.title-sub { font-size: 1rem; color: var(--secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.1em; }
.title-main { font-size: 3rem; font-weight: 800; margin: 0; }

.filter-tabs-container {
  margin-bottom: 4rem;
  overflow-x: auto;
  padding-bottom: 1rem;
}

.filter-tabs {
  display: flex;
  gap: 1rem;
}

.tab-btn {
  padding: 0.8rem 2rem;
  border-radius: 50px;
  border: 1px solid var(--border);
  background: transparent;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tab-btn.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.products-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2.5rem;
}

/* Animations for grid items */
.grid-enter-active, .grid-leave-active { transition: all 0.5s ease; }
.grid-enter-from, .grid-leave-to { opacity: 0; transform: scale(0.9); }

.loading-grid {
  text-align: center;
  padding: 5rem 0;
}

.grid-spinner {
  width: 50px; height: 50px;
  border: 4px solid var(--border);
  border-top-color: var(--secondary);
  border-radius: 50%;
  margin: 0 auto 1.5rem;
  animation: spinner 0.8s linear infinite;
}
@keyframes spinner { to { transform: rotate(360deg); } }

.empty-results {
  text-align: center;
  padding: 5rem 0;
}
.empty-results i { font-size: 4rem; color: #ccc; margin-bottom: 1.5rem; }
.btn-text { background: none; border: none; color: var(--secondary); cursor: pointer; text-decoration: underline; font-weight: 600; margin-top: 1rem; }

.load-more-container {
  margin-top: 5rem;
  text-align: center;
}

.btn-load-more {
  background: transparent;
  border: 2px solid var(--primary);
  color: var(--primary);
  padding: 1.2rem 3rem;
  border-radius: 50px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}
.btn-load-more:hover {
  background: var(--primary);
  color: white;
}
.btn-load-more span { font-weight: 400; opacity: 0.7; margin-left: 0.5rem; }

/* --- PROMO MODAL --- */
.promo-modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.8);
  backdrop-filter: blur(10px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.promo-modal-container {
  background: white;
  width: 100%;
  max-width: 900px;
  border-radius: 32px;
  position: relative;
  overflow: hidden;
  animation: modalScale 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes modalScale { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }

.modal-close-btn {
  position: absolute;
  top: 1.5rem; right: 1.5rem;
  width: 40px; height: 40px;
  border-radius: 50%;
  background: rgba(0,0,0,0.05);
  border: none;
  cursor: pointer;
  font-size: 1.5rem;
  z-index: 10;
}

.promo-modal-content {
  display: flex;
  flex-direction: row;
}

.modal-image-side {
  flex: 1;
  position: relative;
  background: #f8f8f8;
}

.modal-image-side img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.promo-badge-modal {
  position: absolute;
  top: 2rem; left: 2rem;
  background: var(--secondary);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 800;
}

.modal-info-side {
  flex: 1;
  padding: 4rem;
}

.modal-title { font-size: 2.5rem; margin-bottom: 1.5rem; line-height: 1.1; }

.modal-pricing {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 2rem;
}

.m-price-old { font-size: 1.2rem; text-decoration: line-through; color: #aaa; }
.m-price-new { font-size: 2.2rem; font-weight: 800; color: var(--secondary); }

.modal-desc { font-size: 1.1rem; line-height: 1.6; color: #555; margin-bottom: 3rem; }

.modal-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.btn-modal-primary {
  background: var(--primary);
  color: white;
  padding: 1.2rem;
  border-radius: 50px;
  border: none;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  cursor: pointer;
}

.btn-modal-link {
  text-align: center;
  color: var(--primary);
  text-decoration: underline;
  font-weight: 600;
}

/* --- RESPONSIVE --- */
@media (max-width: 1024px) {
  .hero-slide__title { font-size: 3rem; }
  .promo-modal-content { flex-direction: column; }
  .modal-image-side { height: 300px; }
  .modal-info-side { padding: 2rem; }
}

@media (max-width: 768px) {
  .hero-section { height: 70vh; }
  .hero-slide__glass-panel { padding: 1.5rem; }
  .hero-slide__title { font-size: 2.2rem; }
  .hero-actions { flex-direction: column; }
  .section-header-modern { flex-direction: column; align-items: flex-start; gap: 1.5rem; }
  .search-bar-modern { width: 100%; }
}
</style>
