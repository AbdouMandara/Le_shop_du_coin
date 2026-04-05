<template>
  <div class="product-detail-view" v-if="product">
    <div class="main-container">
      <!-- Header / Nav -->
      <div class="nav-header">
        <button @click="$router.back()" class="back-link">
          <i class='bx bx-left-arrow-alt'></i> Retour
        </button>
      </div>

      <div class="product-grid">
        <!-- Image Section -->
        <div class="image-gallery">
          <div class="main-image-container">
            <img :src="getProductImage" :alt="product.name" class="main-image">
            <button 
              class="favorite-toggle" 
              @click="favStore.toggleFavorite(product.id)"
              :class="{ 'active': isFavorite }"
            >
              <i :class="isFavorite ? 'bx bxs-heart' : 'bx bx-heart'"></i>
            </button>
          </div>
        </div>

        <!-- Info Section -->
        <div class="product-info">
          <div class="info-top">
            <span class="category-tag">{{ product.category.label || 'Général' }}</span>
            <div class="rating-block">
              <div class="stars">
                <i v-for="i in 5" :key="i" class='bx' 
                   :class="getStarClass(i, product.avg_rating || product.rating || 0)"></i>
              </div>
              <span class="rating-text">{{ (product.avg_rating || product.rating || 0).toFixed(1) }}</span>
            </div>
          </div>

          <h1 class="product-name">{{ product.name }}</h1>

          <div class="price-section">
            <div class="prices">
              <span v-if="product.original_price > product.price" class="old-price">
                {{ formatPrice(product.original_price) }} FCFA
              </span>
              <span class="main-price">{{ formatPrice(product.price) }} FCFA</span>
            </div>
          </div>

          <div class="divider"></div>

          <div class="description">
            <h3>Description</h3>
            <p>{{ product.description || "Ce produit est soigneusement sélectionné pour sa qualité et sa durabilité. Profitez d'une expérience d'achat unique." }}</p>
          </div>

          <div class="divider"></div>

          <div class="cta-section">
            <div class="qty-selection" v-if="cartItem">
              <button @click="updateQty(-1)" :disabled="cartItem.quantity <= 1" class="qty-btn">
                <i class='bx bx-minus'></i>
              </button>
              <span class="qty-value">{{ cartItem.quantity }}</span>
              <button @click="updateQty(1)" class="qty-btn">
                <i class='bx bx-plus'></i>
              </button>
            </div>

            <button v-if="!cartItem" class="add-btn" @click="cartStore.addToCart(product)">
              <i class='bx bx-cart-add'></i> Ajouter au panier
            </button>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Section Avis Clients Pleine Largeur (Redesign 1.jpeg) -->
  <div class="reviews-section-wrapper" v-if="product">
    <div class="main-container">
      <div class="reviews-wrapper">
        <div class="reviews-layout">
          <!-- Colonne de gauche (Stats & Bouton) -->
          <div class="reviews-sidebar">
             <h2 class="reviews-title">Avis Clients</h2>
             
             <div class="score-display">
                <span class="big-score">{{ (product.avg_rating || product.rating || 0).toFixed(1) }}</span>
                <div class="score-stars-col">
                   <div class="stars-list">
                     <i v-for="i in 5" :key="i" class='bx' :class="getStarClass(i, product.avg_rating || product.rating || 0)"></i>
                   </div>
                   <span class="review-count">Basé sur {{ product.reviews?.length || 0 }} avis</span>
                </div>
             </div>
             
             <div class="rating-bars">
                <div v-for="star in [5,4,3,2,1]" :key="star" class="rating-bar-row">
                   <span class="star-label">{{ star }} <i class='bx bx-star'></i></span>
                   <div class="bar-container">
                      <div class="bar-fill" :style="{ width: getRatingPercentage(star) + '%' }"></div>
                   </div>
                   <span class="bar-count">{{ getRatingCount(star) }}</span>
                </div>
             </div>

             <div class="write-review-section">
                <h3>Partagez votre opinion</h3>
                <p>Avez-vous utilisé ce produit ? Aidez les autres en partageant votre expérience.</p>
                <button 
                  @click="showReviewModal = true" 
                  class="btn-write-review" 
                  :disabled="!canReview" 
                  :title="!canReview ? 'Vous devez avoir commandé et reçu ce produit pour laisser un avis' : ''"
                >
                   Écrire un avis
                </button>
             </div>
          </div>

          <!-- Colonne de droite (Filtres & Liste) -->
          <div class="reviews-content">
             <div class="review-filters">
                <button class="filter-pill active">Tout</button>
                <button class="filter-pill">Achat vérifié</button>
                <button class="filter-pill" v-for="s in [5,4,3,2,1]" :key="s">{{ s }} Étoiles</button>
             </div>

             <div class="reviews-list-area">
                <div v-if="product.reviews && product.reviews.length > 0" class="reviews-list">
                  <div v-for="review in product.reviews" :key="review.id" class="review-item">
                    <div class="review-header">
                      <span class="reviewer-name">{{ review.user?.name || 'Client' }}</span>
                      <div class="review-stars-display">
                        <i v-for="i in 5" :key="i" class='bx' :class="i <= review.rating ? 'bxs-star' : 'bx-star'"></i>
                      </div>
                    </div>
                    <p class="review-comment-text">{{ review.comment }}</p>
                  </div>
                </div>
                <div v-else class="no-reviews-state">
                   <p>Aucun avis pour ce filtre</p>
                </div>
             </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal pour laisser un avis -->
  <div v-if="showReviewModal" class="modal-overlay" @click.self="showReviewModal = false">
    <div class="modal">
      <div class="modal-header">
        <h2>Laisser un avis</h2>
        <button @click="showReviewModal = false" class="btn-close">&times;</button>
      </div>
      <div class="modal-body">
         <div class="review-form">
            <label>Votre Note :</label>
            <div class="rating-input">
               <i v-for="i in 5" :key="i" class='bx' :class="i <= newReview.rating ? 'bxs-star' : 'bx-star'" @click="newReview.rating = i"></i>
            </div>
            <label>Votre Commentaire :</label>
            <textarea v-model="newReview.comment" placeholder="Sélectionnez les étoiles et partagez votre expérience..." rows="4"></textarea>
         </div>
      </div>
      <div class="modal-footer">
        <button @click="showReviewModal = false" class="btn-secondary">Annuler</button>
        <button @click="submitReview" :disabled="submittingReview || newReview.rating === 0 || !newReview.comment.trim()" class="btn-primary">
          {{ submittingReview ? 'Envoi...' : 'Publier' }}
        </button>
      </div>
    </div>
  </div>
  
  <!-- Loading State -->
  <div v-else-if="loading" class="loading-view">
    <div class="loader-spinner"></div>
    <p>Chargement du produit...</p>
  </div>

  <!-- Error State -->
  <div v-else class="error-view">
    <i class='bx bx-error-circle'></i>
    <h2>Oups !</h2>
    <p>Nous n'avons pas pu charger ce produit.</p>
    <router-link to="/" class="home-btn">Retour à l'accueil</router-link>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import { useRoute } from 'vue-router';
import { useProductStore } from '@/stores/products';
import { useCartStore } from '@/stores/cart';
import { useFavoritesStore } from '@/stores/favorites';
import { useOrderStore } from '@/stores/orders';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const route = useRoute();
const productStore = useProductStore();
const cartStore = useCartStore();
const favStore = useFavoritesStore();
const orderStore = useOrderStore();
const authStore = useAuthStore();

const product = computed(() => productStore.currentProduct);
const loading = ref(true);

const newReview = reactive({ rating: 0, comment: '' });
const submittingReview = ref(false);
const showReviewModal = ref(false);

const canReview = computed(() => {
    if (!authStore.isUser || !product.value) return false;
    return orderStore.orders.some(o => 
        o.product_id === product.value.id && 
        (o.status === 'delivered' || !o.delivery)
    );
});

onMounted(async () => {
    try {
        await productStore.fetchProduct(route.params.id);
        if (authStore.isUser) {
            await orderStore.fetchOrders();
        }
    } catch (err) {
        console.error("Erreur lors du chargement du produit:", err);
    } finally {
        loading.value = false;
    }
});

const isFavorite = computed(() => {
    if (!product.value) return false;
    return favStore.isFavorite(product.value.id);
});

const cartItem = computed(() => {
    if (!product.value) return null;
    return cartStore.items.find(i => i.id === product.value.id);
});

const updateQty = (diff) => {
    if (cartItem.value) {
        const newQty = cartItem.value.quantity + diff;
        if (newQty > 0) {
            cartItem.value.quantity = newQty;
            cartStore.saveCart();
        }
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};

const submitReview = async () => {
    if (!product.value) return;
    submittingReview.value = true;
    try {
        const payload = {
            product_id: product.value.id,
            rating: newReview.rating,
            comment: newReview.comment
        };
        const res = await api.post('/client/reviews', payload);
        if (!product.value.reviews) {
            product.value.reviews = [];
        }
        product.value.reviews.unshift(res.data.data);
        
        // Recalculer la moyenne locale temporairement
        const totalRatings = product.value.reviews.reduce((sum, r) => sum + r.rating, 0);
        product.value.avg_rating = totalRatings / product.value.reviews.length;
        
        // Reset form
        newReview.rating = 0;
        newReview.comment = '';
        showReviewModal.value = false;
    } catch (err) {
        alert("Erreur lors de l'envoi de l'avis");
    } finally {
        submittingReview.value = false;
    }
};

const getRatingCount = (starIndex) => {
    if (!product.value || !product.value.reviews) return 0;
    return product.value.reviews.filter(r => r.rating === starIndex).length;
};

const getRatingPercentage = (starIndex) => {
    if (!product.value || !product.value.reviews || product.value.reviews.length === 0) return 0;
    const count = getRatingCount(starIndex);
    return (count / product.value.reviews.length) * 100;
};

const getProductImage = computed(() => {
    if (!product.value) return '';
    const path = product.value.image;
    if (!path) return 'https://placehold.co/600x600?text=Produit';
    if (path.startsWith('http')) return path;
    return `/storage/${path}`;
});

const getStarClass = (index, rating) => {
    if (rating >= index) return 'bxs-star';
    if (rating >= index - 0.5) return 'bx-star-half';
    return 'bx-star';
};
</script>

<style scoped>
.product-detail-view {
  min-height: calc(100vh - 60px);
  background-color: var(--surface);
  padding: 2rem 0;
}

.main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.nav-header {
  margin-bottom: 2rem;
}

.back-link {
  background: none;
  border: none;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  margin-left: -1rem;
}

.back-link i {
  font-size: 1.5rem;
  transition: transform 0.3s ease;
}

.back-link:hover {
  color: var(--primary);
  background-color: var(--neutral);
}

.back-link:hover i {
  transform: translateX(-5px);
}

.product-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: start;
}

/* Image Section */
.image-gallery {
  position: sticky;
  top: 100px;
}

.main-image-container {
  background-color: white;
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  aspect-ratio: 1;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  border: 1px solid var(--border);
}

.main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.favorite-toggle {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: white;
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #666;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.favorite-toggle.active {
  color: #ff4d4d;
  transform: scale(1.1);
}

/* Info Section */
.product-info {
  display: flex;
  flex-direction: column;
  padding-top: 4.5em;
}

.info-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.category-tag {
  background-color: var(--neutral);
  color: #888;
  padding: 0.4rem 1rem;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.rating-block {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stars {
  color: #FFB800;
  font-size: 1.1rem;
}

.rating-text {
  font-weight: 700;
  color: #888;
}

.product-name {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  color: var(--text);
  line-height: 1.1;
}

.price-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.prices {
  display: flex;
  flex-direction: row-reverse;
  align-items: center;
  gap : 0.75em;
}

.main-price {
  font-size: 2.25rem;
  font-weight: 800;
  color: var(--primary);
}

.old-price {
  font-size: 1.1rem;
  color: #999;
  text-decoration: line-through;
  font-weight: 500;
}

.discount-badge {
  background-color: var(--secondary);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 800;
  font-size: 0.85rem;
}

.divider {
  height: 1px;
  background-color: var(--border);
}

.description h3 {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.description p {
  color: #666;
  line-height: 1.8;
  font-size: 1.05rem;
}

.cta-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-top: 2rem;
}

.qty-selection {
  display: flex;
  justify-content: space-between;
  width: 100%;
  align-items: center;
  gap: 1rem;
  background-color: var(--neutral);
  padding: 0.5rem;
  border-radius: 50px;
  border: 1px solid var(--border);
}

.qty-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid var(--border);
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.qty-btn:hover:not(:disabled) {
  background-color: var(--primary);
  color: white;
}

.qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.qty-value {
  font-weight: 700;
  font-size: 1.2rem;
  min-width: 30px;
  text-align: center;
}

.add-btn {
  flex: 1;
  background-color: var(--primary);
  color: white;
  border: none;
  height: 56px;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  cursor: pointer;
}

.already-in-cart {
  flex: 1;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  color: #2ecc71;
  font-weight: 700;
  font-size: 1.1rem;
  background-color: #e8f8f0;
  border-radius: 50px;
}

.trust-badges {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  text-align: center;
  padding: 1rem;
  background-color: var(--neutral);
  border-radius: 12px;
  border: 1px solid var(--border);
}

.badge i {
  font-size: 1.5rem;
  color: var(--primary);
}

.badge span {
  font-size: 0.75rem;
  font-weight: 600;
  color: #666;
}

/* States */
.loading-view, .error-view {
  min-height: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  text-align: center;
}

.loader-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--neutral);
  border-top: 4px solid var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.error-view i {
  font-size: 4rem;
  color: #ff4d4d;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 992px) {
  .product-grid {
    grid-template-columns: 1fr;
    gap: 3rem;
  }
  
  .image-gallery {
    position: relative;
    top: 0;
  }
}

@media (max-width: 576px) {
  .product-name {
    font-size: 1.75rem;
  }
  
  .cta-section {
    flex-direction: column;
  }
  
  .qty-selection, .add-btn, .already-in-cart {
    width: 100%;
  }
}

/* Styles de la modale */
.btn-secondary {
  padding: 0.8rem 1.5rem;
  background-color: transparent;
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background-color: var(--neutral);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  animation: fadeIn 0.2s ease-out;
}

.modal {
  background: var(--surface);
  border-radius: 16px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
  overflow: hidden;
  animation: slideUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: var(--text);
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #888;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

@keyframes fadeIn {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

@keyframes slideUp {
  0% { transform: translateY(20px); opacity: 0; }
  100% { transform: translateY(0); opacity: 1; }
}

.reviews-section-wrapper {
  background-color: var(--surface);
  border-top: 1px solid var(--border);
  padding: 4rem 0;
}

/* Layout Redesign Avis (Copie 1.jpeg) */
.reviews-wrapper {
  background-color: white;
  border-radius: 12px;
  padding: 3rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}

.reviews-layout {
  display: flex;
  gap: 3rem;
}

.reviews-sidebar {
  width: 320px;
  min-width: 320px;
  display: flex;
  flex-direction: column;
}

.reviews-title {
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  color: var(--text);
}

.score-display {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.big-score {
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1;
  color: var(--text);
}

.score-stars-col {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stars-list {
  color: #fb923c;
  font-size: 1.1rem;
  display: flex;
  gap: 2px;
}

.review-count {
  font-size: 0.85rem;
  color: #888;
}

.rating-bars {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 2.5rem;
}

.rating-bar-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9rem;
}

.star-label {
  display: flex;
  align-items: center;
  gap: 4px;
  min-width: 30px;
  justify-content: flex-end;
  color: #555;
  font-weight: 500;
}

.star-label i {
  color: #999;
}

.bar-container {
  flex: 1;
  height: 8px;
  background-color: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background-color: #d1d5db;
  border-radius: 4px;
  transition: width 0.5s ease-out;
}

.bar-count {
  min-width: 25px;
  color: #666;
  text-align: left;
}

.write-review-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.write-review-section h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.write-review-section p {
  font-size: 0.9rem;
  color: #666;
  line-height: 1.5;
  margin: 0 0 0.5rem 0;
}

.btn-write-review {
  background-color: transparent;
  color: var(--text);
  border: 1px solid var(--text);
  padding: 0.8rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-write-review:hover:not(:disabled) {
  background-color: var(--neutral);
}

.btn-write-review:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  border-color: #ccc;
  color: #999;
}

.reviews-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.review-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 2rem;
  margin-top: calc(1.5rem + 3px); /* pour l'aligner avec le top du score */
}

.filter-pill {
  background: white;
  border: 1px solid #e5e7eb;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-pill.active {
  background: #1f2937;
  color: white;
  border-color: #1f2937;
}

.filter-pill:hover:not(.active) {
  background: #f9fafb;
}

.reviews-list-area {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.no-reviews-state {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  color: #9ca3af;
  font-size: 0.95rem;
  min-height: 200px;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.review-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.review-header {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.reviewer-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text);
}

.review-stars-display {
  color: #fb923c;
  font-size: 0.9rem;
}

.review-comment-text {
  font-size: 0.95rem;
  line-height: 1.6;
  color: #4b5563;
  margin: 0;
}

.review-form {
  display: flex;
  flex-direction: column;
}

.review-form label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.rating-input {
  display: flex;
  gap: 0.25rem;
  font-size: 2rem;
  color: #fb923c;
  margin-bottom: 1.5rem;
  cursor: pointer;
}

.review-form textarea {
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--background);
  color: var(--text);
  font-family: inherit;
  font-size: 0.95rem;
  resize: vertical;
}

.review-form textarea:focus {
  border-color: var(--primary);
  outline: none;
}

/* Responsive pour la section avis */
@media (max-width: 768px) {
  .reviews-layout {
    flex-direction: column;
    gap: 2rem;
  }
  
  .reviews-sidebar {
    width: 100%;
    min-width: auto;
  }
  
  .review-filters {
    margin-top: 0;
  }
  
  .reviews-wrapper {
    margin-top: 2rem;
    padding-top: 2rem;
  }
}
</style>
