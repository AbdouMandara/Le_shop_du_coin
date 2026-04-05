<template>
  <div class="home-view">
    <!-- HERO SECTION (Promotional Carousel) -->
    <section class="hero-section" :class="{ 'has-carousel': productStore.promotionalProducts.length > 0 }">
      <div v-if="productStore.loadingPromotions" class="hero-loader">
        <div class="loader-ripple"><div></div><div></div></div>
      </div>
      
      <div v-else-if="productStore.promotionalProducts.length > 0" class="hero-container container">

        <!-- Marquee Produit en solde -->
        <div class="promo-marquee">
          <div class="marquee-content">
            <span v-for="n in 10" :key="n"> 🔥 Produit en solde -20% 🔥 </span>
          </div>
        </div>

        <div class="hero-card-carousel">
          <swiper
            :modules="[Autoplay, Pagination, Navigation, FreeMode, Mousewheel]"
            v-bind="swiperOptions"
            class="hero-swiper"
          >
            <swiper-slide 
              v-for="product in productStore.promotionalProducts" 
              :key="product.id"
              class="hero-promo-slide"
            >
              <div class="promo-container">
                <div class="promo-visual">
                  <img :src="getProductImage(product.image)" :alt="product.name" @error="handleImgError" />
                  <div class="promo-badge-float" v-if="product.original_price > product.price">
                    -{{ Math.round(((product.original_price - product.price) / product.original_price) * 100) }}%
                  </div>
                </div>
                
                <div class="promo-content">
                  <div class="promo-content-inner">
                    <span class="promo-label">{{product.active_promotion.name}}</span>
                    <h2 class="promo-title">{{ product.name }}</h2>
                    <p class="promo-description">{{ product.description }}</p>
                    
                    <div class="promo-pricing-hero">
                      <div class="price-main">{{ formatPrice(product.price) }} <small>FCFA</small></div>
                      <div class="price-old" v-if="product.original_price > product.price">
                        {{ formatPrice(product.original_price) }} FCFA
                      </div>
                    </div>
                    
                    <div class="promo-actions-hero">
                      <button @click="addToCart(product)" class="btn-buy-hero">
                        <i class='bx bx-cart-add'></i> Ajouter au Panier
                      </button>
                      <router-link :to="`/product/${product.id}`" class="btn-details-hero">
                         Détails du Produit
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
            </swiper-slide>
          </swiper>

          <!-- Swiper Controls preserved but moved to sides if needed -->
          <div class="hero-carousel__dots-vertical"></div>
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
    <!-- <section class="usp-section">
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
    </section> -->

    <!-- PRODUCT DISCOVERY SECTION -->
    <section id="discovery" class="discovery-section">
      <div class="container">
        <div class="section-header-modern">
          <div class="header-left">
            <h2 class="title-sub">Découvrez notre collection</h2>
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
           <p>Chargement des produits ...</p>
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
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination, Navigation, FreeMode, Mousewheel } from 'swiper/modules';
import { useProductStore } from '@/stores/products';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import ProductCard from '@/components/ProductCard.vue';
import 'swiper/css';
import 'swiper/css/pagination';

const productStore = useProductStore();
const authStore = useAuthStore();
const cartStore = useCartStore();

// --- SWIPER CONFIGURATION ---
const swiperOptions = {
    direction: 'vertical',
    slidesPerView: 1,
    spaceBetween: 0,
    mousewheel: true,
    keyboard: true,
    loop: true,
    speed: 1000,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },
    pagination: {
        el: '.hero-carousel__dots-vertical',
        clickable: true,
        bulletClass: 'hero-dot-v',
        bulletActiveClass: 'active',
    },
};

// Navigation logic based on role
const productsUrl = computed(() => {
    if (authStore.isAuthenticated) {
        if (authStore.isAdmin) return '/admin/products';
        if (authStore.isLivreur) return '/livreur/products';
        if (authStore.isUser) return '/client/products';
    }
    return '/products';
});

// --- REMOVED OBSOLETE CAROUSEL LOGIC ---


// --- REMOVED OBSOLETE AUTOPLAY LOGIC ---

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
});

onUnmounted(() => {
    // Swiper cleanup is handled automatically by the component
});


// Watchers
watch(activeCategory, () => {
  visibleCount.value = 10
});
</script>

<style scoped>
.home-view {
  display: flex;
  flex-direction: column;
  gap: 2em;
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
  background-color: var(--primary);
  min-height: 95lvh;
  display: flex;
  align-items: center;
  overflow: hidden;
  color: white;
}
.hero-loader {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 400px;
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
  left : 50%;
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
.hero-swiper {
  width: 100%;
  height: 500px; /* Reduced height */
  border-radius: 24px;
  overflow: hidden;
  background: transparent;
}

/* Marquee Styling */
.promo-marquee {
  overflow: hidden;
  white-space: nowrap;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50px;
  padding: 0.5rem 0;
  margin-bottom: 1.5rem;
  display: flex;
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.marquee-content {
  display: flex;
  animation: marquee 25s linear infinite;
  min-width: 200%;
}
.marquee-content span {
  padding: 0 2rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  font-size: 0.9rem;
}
@keyframes marquee {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

.hero-promo-slide {
  height: 100%;
  width: 100%;
}

.promo-container {
  display: flex;
  height: 100%;
  width: 100%;
}

.promo-visual {
  width : 50%;
  position: relative;
  /* background: rgba(255, 255, 255, 0.03); */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2.5rem;
  overflow: hidden;
}

.promo-visual img {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
  filter: drop-shadow(0 20px 50px rgba(0,0,0,0.1));
}

.promo-badge-float {
  position: absolute;
  top: 3rem; left: 3rem;
  background: var(--secondary);
  color: white;
  padding: 10px 20px;
  border-radius: 12px;
  font-size: 1.2rem;
  z-index: 5;
}

.promo-content {
  width : 50%;
  display: flex;
  align-items: center;
  padding: 3rem;
  position: relative;
}

.promo-content::before {
  content: '';
  position: absolute;
  left: 0; top: 15%;
  height: 70%; width: 1px;
  background: rgba(255,255,255,0.1);
}

.promo-content-inner {
  width : 100%;
}

.promo-label {
  display: inline-block;
  color: var(--secondary);
  font-weight: 700;
  letter-spacing: 0.15em;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
  text-transform: uppercase;
}

.promo-title {
  font-size: clamp(2rem, 3vw, 2.5rem); /* Smaller price */
  font-weight: 500;
  color: white;
  line-height: 1.1;
  margin-bottom: 1.5rem;
}

.promo-description {
  font-size: 1rem;
  color: rgba(255,255,255,0.6);
  line-height: 1.5;
  margin-bottom: 2rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.promo-pricing-hero {
    margin-bottom: 2.5rem;
    display: flex;
    gap: 2em;
    align-items: center;
}

.price-main {
  font-size: 3.2rem;
  font-weight: 800;
  color: white;
}

.price-main small { font-size: 1rem; margin-left: 0.4rem; color: rgba(255,255,255,0.4); }

.price-old {
  font-size: 1.4rem;
  text-decoration: line-through;
  color: #bbb;
  margin-top: 0.5rem;
}

.promo-actions-hero {
  display: flex;
  gap: 1.5rem;
}

.btn-buy-hero {
    flex: 1;
    background: var(--background);
    color: var(--primary);
    border: none;
    padding: 0.75em;
    border-radius: 100px;
    font-size: 1.1rem;
    font-weight : 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition : all 0.25s ease-in-out; 

    &:hover{
      background: rgba(255,255,255,0.75);
    }
}

.btn-details-hero {
    flex: 1;
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.1);
    /* padding: 1.25rem 2rem; */
    padding: 0.75rem;
    border-radius: 2em;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-details-hero:hover {
  border-color: white;
  background: rgba(255,255,255,0.05);
}

/* Vertical Pagination Styling */
.hero-carousel__dots-vertical {
  position: absolute;
  right: 2rem;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  z-index: 100;
}

:deep(.hero-dot-v) {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255,255,255,0.1);
  cursor: pointer;
  transition: all 0.4s ease;
  border: 1px solid rgba(255,255,255,0.1);
}

:deep(.hero-dot-v.active) {
  background: var(--background);
  height: 35px;
  border-radius: 10px;
  box-shadow: 0 10px 20px rgba(255, 107, 53, 0.2);
}

@media (max-width: 1024px) {
  .hero-swiper { height: auto; border-radius: 20px; }
  .promo-container { flex-direction: column; }
  .promo-visual { padding: 3rem; min-height: 350px; }
  .promo-content { padding: 3rem; }
  .promo-actions-hero { flex-direction: column; }
  .hero-carousel__dots-vertical { display: none; }
}

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

.section-header-modern {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 2rem;
}

.title-sub { font-size: 1rem; color: var(--secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.1em; }
.title-main { font-size: 3rem; font-weight: 800; margin: 0; }

.filter-tabs-container {
  margin-bottom: 1rem;
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
</style>