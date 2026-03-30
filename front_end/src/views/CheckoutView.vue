<template>
  <div class="checkout-page">
    <header class="page-header">
      <h1 v-if="step === 1">Mode de récupération</h1>
      <h1 v-else-if="step === 2">Lieu de Livraison</h1>
      <h1 v-else-if="step === 3">Résumé de votre commande</h1>
      
      <p v-if="step === 1">Choisissez comment vous souhaitez récupérer votre commande</p>
      <p v-else-if="step === 2">Indiquez l'emplacement exact pour la livraison</p>
      <p v-else-if="step === 3">Vérifiez les détails avant de valider</p>
    </header>

    <div class="checkout-container-single">

      <!-- STEP 1: Choix de la livraison -->
      <div v-if="step === 1" class="checkout-step">
        <div class="delivery-choices">
          <div 
            class="choice-card" 
            :class="{ active: deliveryMode === 'sans_livraison' }"
            @click="deliveryMode = 'sans_livraison'"
          >
            <i class='bx bx-store-alt choice-icon'></i>
            <span>Sans Livraison</span>
            <small>Récupération en magasin (Gratuit)</small>
          </div>
          <div 
            class="choice-card" 
            :class="{ active: deliveryMode === 'avec_livraison' }"
            @click="deliveryMode = 'avec_livraison'"
          >
            <i class='bx bx-car choice-icon'></i>
            <span>Avec Livraison</span>
            <small>Livré à votre adresse</small>
          </div>
        </div>

        <button 
          class="btn-checkout mt-4" 
          @click="goNextStep" 
          :disabled="loading"
        >
            <span>Continuer</span>
        </button>
      </div>

      <!-- STEP 2: Carte pour la livraison -->
      <div v-if="step === 2" class="checkout-step">
        <div class="map-section">
          <h4>Sélectionnez votre lieu de livraison sur la carte :</h4>
          <p class="map-hint">Cliquez sur la carte pour placer le repère. La boutique (CTEC Sarl) est indiquée par le repère d'origine.</p>
          
          <div class="map-container">
            <l-map ref="map" v-model:zoom="zoom" :center="center" @click="onMapClick">
              <l-tile-layer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
              ></l-tile-layer>

              <!-- Fixed Shop Location -->
              <l-marker :lat-lng="shopLocation">
                  <l-tooltip>Boutique CTEC Sarl</l-tooltip>
              </l-marker>

              <!-- User Selection Location -->
              <l-marker v-if="marker" :lat-lng="marker">
                  <l-tooltip>Votre adresse de livraison</l-tooltip>
              </l-marker>
              
              <!-- Distance Line -->
              <l-polyline v-if="marker" :lat-lngs="[shopLocation, marker]" color="#FF5722" :weight="3" dashArray="10, 10"></l-polyline>
            </l-map>
          </div>

          <div class="coords-info">
            <p v-if="marker" class="coords-display">
                <i class='bx bx-target-lock' ></i> Coordonnées : {{ marker.lat.toFixed(4) }}, {{ marker.lng.toFixed(4) }}
            </p>
            <p v-else class="coords-error">
                <i class='bx bx-error-circle'></i> Veuillez sélectionner un point sur la carte.
            </p>
            
            <p v-if="distanceKm" class="distance-display">
                <i class='bx bx-map-alt' ></i> Distance estimée : <strong>{{ distanceKm }} km</strong> (en ligne droite)
            </p>
          </div>
        </div>

        <div class="step-actions mt-4">
          <button class="btn-secondary" @click="step = 1" :disabled="loading">Retour</button>
          
          <button 
            class="btn-checkout flex-1" 
            @click="step = 3" 
            :disabled="loading || !marker"
          >
              <span>Continuer vers le résumé</span>
          </button>
        </div>
      </div>
 
      <!-- STEP 3: Résumé final -->
      <div v-if="step === 3" class="checkout-step">
        <div class="summary-details">
            <div class="summary-section">
                <h4><i class='bx bx-shopping-bag'></i> Articles</h4>
                <div class="summary-items">
                    <div v-for="item in cartStore.items" :key="item.id" class="summary-item">
                        <span class="item-qty">{{ item.quantity }}x</span>
                        <span class="item-name">{{ item.name }}</span>
                        <span class="item-total">{{ item.price * item.quantity }} FCFA</span>
                    </div>
                </div>
            </div>
 
            <div class="summary-section">
                <h4><i class='bx bx-map-pin'></i> Récupération</h4>
                <p v-if="deliveryMode === 'sans_livraison'">
                    <i class='bx bx-store-alt'></i> Retrait en magasin (Gratuit)
                </p>
                <div v-else>
                    <p><i class='bx bx-car'></i> Livraison à domicile</p>
                    <small v-if="marker" class="text-muted">
                        Distance : {{ distanceKm }} km ({{ marker.lat.toFixed(4) }}, {{ marker.lng.toFixed(4) }})
                    </small>
                </div>
            </div>
 
            <div class="summary-total-box">
                <div class="total-row">
                    <span>Sous-total</span>
                    <span>{{ cartStore.totalPrice }} FCFA</span>
                </div>
                <div class="total-row">
                    <span>Frais de livraison</span>
                    <span :class="{ 'free': deliveryFee === 0 }">
                        {{ deliveryFee > 0 ? deliveryFee + ' FCFA' : 'Gratuit' }}
                    </span>
                </div>
                <hr>
                <div class="total-row grand-total">
                    <span>TOTAL À PAYER</span>
                    <span>{{ totalToPay }} FCFA</span>
                </div>
            </div>
        </div>
 
        <div class="step-actions mt-4">
          <button class="btn-secondary" @click="deliveryMode === 'avec_livraison' ? step = 2 : step = 1" :disabled="loading">Retour</button>
          
          <button 
            class="btn-checkout flex-1 btn-validate" 
            @click="handleConfirm" 
            :disabled="loading"
          >
              <span v-if="!loading">Valider et commander</span>
              <i v-else class='bx bx-loader-alt bx-spin'></i>
          </button>
        </div>
      </div>

    </div>

    <!-- SUCCESS ANIMATION OVERLAY -->
    <Teleport to="body">
        <div v-if="isOrderSuccess" class="success-overlay">
            <div class="success-card">
                <Vue3Lottie 
                    :animation-data="OrderSuccessJSON"
                    :height="300"
                    :width="300"
                    :loop="false"
                />
                <h2>Commande validée !</h2>
                <p>Merci pour votre achat. Nous préparons votre commande.</p>
            </div>
        </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useOrderStore } from '@/stores/orders';
import { useRouter } from 'vue-router';

// Leaflet imports
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPolyline, LTooltip } from "@vue-leaflet/vue-leaflet";
import L from "leaflet";
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

// Lottie
import OrderSuccessJSON from '@/assets/animation_commande_soumisse_lottie.json';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl,
  iconUrl,
  shadowUrl,
});

const cartStore = useCartStore();
const orderStore = useOrderStore();
const router = useRouter();

const loading = ref(false);
const isOrderSuccess = ref(false);
const step = ref(1);
const deliveryMode = ref('sans_livraison'); 

// Map configuration
const zoom = ref(13);
// Shop location 
const shopLocation = ref([4.038026, 9.741443]);
const center = ref([4.038026, 9.741443]);
const marker = ref(null);

onMounted(() => {
    if (cartStore.items.length === 0) {
        router.push('/client/cart');
    }
});

const onMapClick = (e) => {
    marker.value = e.latlng;
};

// Calculate distance in KM using Leaflet's built in distanceTo
const distanceKm = computed(() => {
    if (!marker.value) return null;
    const shopPoint = L.latLng(shopLocation.value[0], shopLocation.value[1]);
    const userPoint = L.latLng(marker.value.lat, marker.value.lng);
    const meters = shopPoint.distanceTo(userPoint);
    return (meters / 1000).toFixed(2);
});

const deliveryFee = computed(() => {
    if (deliveryMode.value === 'sans_livraison') return 0;
    if (distanceKm.value && parseFloat(distanceKm.value) >= 1) {
        return 450;
    }
    return 0;
});

const totalToPay = computed(() => {
    return cartStore.totalPrice + deliveryFee.value;
});

const goNextStep = async () => {
    if (deliveryMode.value === 'sans_livraison') {
        step.value = 3;
    } else {
        step.value = 2;
    }
};

const handleConfirm = async () => {
    if (deliveryMode.value === 'avec_livraison' && !marker.value) {
        alert("Veuillez sélectionner un point sur la carte pour la livraison.");
        return;
    }

    loading.value = true;
    try {
        const isDelivery = deliveryMode.value === 'avec_livraison';
        // Add distance to the payload if we want, but for now just location
        const locationStr = isDelivery ? JSON.stringify({ 
            lat: marker.value.lat, 
            lng: marker.value.lng, 
            distanceKm: distanceKm.value,
            deliveryFee: deliveryFee.value 
        }) : null;

        for (const item of cartStore.items) {
            for (let i = 0; i < item.quantity; i++) {
                await orderStore.placeOrder(item.id, isDelivery, locationStr);
            }
        }
        
        // --- SUCCESS SEQUENCE ---
        isOrderSuccess.value = true;
        
        // Wait 3 seconds for the animation to be seen
        await new Promise(r => setTimeout(r, 3500));
        
        cartStore.items = [];
        cartStore.saveCart();
        router.push({ name: 'client-orders' });
    } catch (err) {
        console.error('Checkout error:', err.response?.data || err);
        alert('Erreur lors de la commande: ' + (err.response?.data?.message || err.message));
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.checkout-page {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
}

.page-header {
    text-align: center;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
}

.page-header p {
    color: #888;
    margin-top: 0.5rem;
}

.checkout-container-single {
    width: 100%;
    max-width: 800px;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

.checkout-step {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.delivery-choices {
    display: flex;
    gap: 1.5rem;
}

.choice-card {
    flex: 1;
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    cursor: pointer;
    background-color: var(--surface);
    transition: all 0.2s ease;
}

.choice-card:hover {
    border-color: var(--primary);
    background-color: var(--neutral);
}

.choice-card.active {
    border-color: var(--primary);
    background-color: rgba(var(--primary-rgb), 0.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.choice-icon {
    font-size: 3rem;
    color: var(--primary);
}

.choice-card span {
    font-size: 1.2rem;
    font-weight: bold;
}

.choice-card small {
    color: #888;
    text-align: center;
}

.map-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.map-section h4 {
    margin: 0;
    font-size: 1.25rem;
}

.map-hint {
    color: #666;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}

.map-container {
    height: 550px;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid var(--border);
}

.coords-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    background-color: var(--neutral);
    padding: 1rem;
    border-radius: 8px;
    margin-top: 0.5rem;
}

.coords-info p {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.coords-info i {
    font-size: 1.25rem;
}

.coords-display {
    color: var(--success, #4CAF50);
}

.coords-error {
    color: var(--error, #f44336);
}

.distance-display {
    color: var(--primary);
    font-weight: 500;
}

.step-actions {
    display: flex;
    gap: 1rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.flex-1 {
    flex: 1;
}

.btn-checkout {
    width: 100%;
    background-color: var(--primary);
    color: #FFFFFF;
    border: none;
    padding: 1.15rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: opacity 0.2s;
}

.btn-checkout:hover:not(:disabled) {
    opacity: 0.9;
}

.btn-checkout:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.btn-secondary {
    background-color: #FFFFFF;
    color: var(--text);
    border: 2px solid var(--border);
    padding: 1.15rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover:not(:disabled) {
    background-color: var(--neutral);
    border-color: #ccc;
}
 
/* Summary Styles */
.summary-details {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
 
.summary-section {
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}
 
.summary-section h4 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0 0 1rem;
    color: var(--primary);
}
 
.summary-items {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
 
.summary-item {
    display: grid;
    grid-template-columns: 40px 1fr auto;
    gap: 1rem;
    font-size: 1.05rem;
}
 
.item-qty {
    font-weight: bold;
    color: var(--secondary);
}
 
.item-total {
    font-weight: 600;
}
 
.summary-total-box {
    background-color: var(--neutral);
    padding: 2rem;
    border-radius: 12px;
}
 
.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 1.1rem;
}
 
.grand-total {
    margin-top: 1rem;
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--primary);
}
 
.btn-validate {
    background-color: #4CAF50 !important;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}
 
.text-muted {
    color: #888;
}

/* SUCCESS OVERLAY STYLES */
.success-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

[data-theme='dark'] .success-overlay {
    background: rgba(0, 0, 0, 0.8);
}

.success-card {
    text-align: center;
    background: var(--surface);
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    max-width: 500px;
    width: 90%;
    animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes scaleIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.success-card h2 {
    font-size: 2rem;
    margin: 1.5rem 0 1rem;
    color: var(--primary);
}

.success-card p {
    color: #666;
    font-size: 1.1rem;
    line-height: 1.6;
}
</style>
