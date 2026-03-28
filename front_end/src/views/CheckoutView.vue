<template>
  <div class="checkout-page">
    <header class="page-header">
      <h1 v-if="step === 1">Options de Livraison</h1>
      <h1 v-else-if="step === 2">Lieu de Livraison</h1>
      
      <p v-if="step === 1">Choisissez comment vous souhaitez récupérer votre commande</p>
      <p v-else-if="step === 2">Indiquez l'emplacement exact pour la livraison</p>
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
            <span v-if="!loading">{{ deliveryMode === 'avec_livraison' ? 'Continuer' : 'Confirmer la commande' }}</span>
            <i v-else class='bx bx-loader-alt bx-spin'></i>
        </button>
      </div>

      <!-- STEP 2: Carte pour la livraison -->
      <div v-if="step === 2" class="checkout-step">
        <div class="map-section">
          <h4>Sélectionnez votre lieu de livraison sur la carte :</h4>
          <p class="map-hint">Cliquez sur la carte pour placer le repère</p>
          <div class="map-container">
            <l-map ref="map" v-model:zoom="zoom" :center="center" @click="onMapClick">
              <l-tile-layer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
              ></l-tile-layer>
              <l-marker v-if="marker" :lat-lng="marker"></l-marker>
            </l-map>
          </div>
          <p v-if="marker" class="coords-display">Coordonnées : {{ marker.lat.toFixed(4) }}, {{ marker.lng.toFixed(4) }}</p>
          <p v-else class="coords-error">Veuillez sélectionner un point sur la carte.</p>
        </div>

        <div class="step-actions mt-4">
          <button class="btn-secondary" @click="step = 1" :disabled="loading">Retour</button>
          
          <button 
            class="btn-checkout flex-1" 
            @click="handleConfirm" 
            :disabled="loading || !marker"
          >
              <span v-if="!loading">Confirmer la commande</span>
              <i v-else class='bx bx-loader-alt bx-spin'></i>
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useOrderStore } from '@/stores/orders';
import { useRouter } from 'vue-router';

// Leaflet imports
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from "leaflet";
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

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
const step = ref(1);
const deliveryMode = ref('sans_livraison'); 

const zoom = ref(13);
const center = ref([14.6928, -17.4467]);
const marker = ref(null);

onMounted(() => {
    if (cartStore.items.length === 0) {
        router.push('/client/cart');
    }
});

const onMapClick = (e) => {
    marker.value = e.latlng;
};

const goNextStep = async () => {
    if (deliveryMode.value === 'sans_livraison') {
        // Direct checkout
        await handleConfirm();
    } else {
        // Go to map
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
        const locationStr = isDelivery ? JSON.stringify({ lat: marker.value.lat, lng: marker.value.lng }) : null;

        for (const item of cartStore.items) {
            for (let i = 0; i < item.quantity; i++) {
                await orderStore.placeOrder(item.id, isDelivery, locationStr);
            }
        }
        
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
    max-width: 600px;
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
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
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
    gap: 0.5rem;
}

.map-section h4 {
    margin: 0;
}

.map-hint {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.map-container {
    height: 400px;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border);
}

.coords-display {
    color: var(--success, #4CAF50);
    font-weight: 500;
    margin-top: 0.5rem;
    font-size: 0.95rem;
}

.coords-error {
    color: var(--error, #f44336);
    font-weight: 500;
    margin-top: 0.5rem;
    font-size: 0.95rem;
}

.step-actions {
    display: flex;
    gap: 1rem;
}

.mt-4 {
    margin-top: 1rem;
}

.flex-1 {
    flex: 1;
}

.btn-checkout {
    width: 100%;
    background-color: var(--primary);
    color: #FFFFFF;
    border: none;
    padding: 1rem;
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
    background-color: var(--neutral);
    color: var(--text);
    border: 1px solid var(--border);
    padding: 1rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-secondary:hover:not(:disabled) {
    background-color: var(--border);
}
</style>
