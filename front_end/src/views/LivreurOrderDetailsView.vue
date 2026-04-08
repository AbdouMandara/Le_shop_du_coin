<template>
  <div class="livreur-details-page">
    <header class="page-header">
      <div class="header-top-row">
          <button class="btn-back" @click="$router.back()">
              <i class='bx bx-arrow-back'></i> Retour
          </button>
          
          <!-- DYNAMIC ACTION BUTTON -->
          <div v-if="selectedOrder && !loading" class="header-action-wrapper">
              <button 
                v-if="['paid', 'pending'].includes(selectedOrder.status)" 
                @click="handleStatusUpdate('in_transit')"
                class="btn-action btn-start"
                :disabled="updating"
              >
                  {{ updating ? 'Chargement...' : 'Démarrer la livraison' }}
              </button>
              
              <button 
                v-else-if="selectedOrder.status === 'in_transit'" 
                @click="handleStatusUpdate('arrived')"
                class="btn-action btn-complete"
                :disabled="updating"
              >
                  {{ updating ? 'Chargement...' : 'Commande livrée' }}
              </button>
              
              <div v-else-if="['arrived', 'delivered'].includes(selectedOrder.status)" class="delivery-complete-badge">
                  <i class='bx bxs-check-circle'></i> 
                  {{ selectedOrder.status === 'arrived' ? 'Livraison effectuée (attente client)' : 'Cette commande a été livrée' }}
              </div>
          </div>
      </div>
      <h1>Détails de la Commande #{{ orderId?.substring(0,8) }}</h1>
      <p>Visualisez les informations du client et son lieu de livraison.</p>
    </header>

    <div v-if="loading" class="loading">
        <i class='bx bx-loader-alt bx-spin'></i> Chargement des détails...
    </div>
    
    <div v-else-if="!selectedOrder" class="empty">
        <i class='bx bx-error'></i>
        <p>Commande introuvable ou non autorisée.</p>
    </div>

    <div v-else class="details-container">
        <!-- Carte de livraison -->
        <div class="map-section">
            <h3>Lieu de livraison</h3>
            <template v-if="deliveryLocation">
                 <p v-if="deliveryLocation.distanceKm && !currentLocation" class="distance-info">
                     Distance estimée : <strong>{{ deliveryLocation.distanceKm }} km</strong>
                 </p>
                 <p v-if="currentLocation" class="distance-info live-status">
                     <i class='bx bxs-navigation bx-fade-right'></i> Tracking en temps réel activé
                 </p>
                 <div class="map-container">
                    <l-map ref="map" v-model:zoom="zoom" :center="mapCenter">
                        <l-tile-layer
                            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                            layer-type="base"
                            name="OpenStreetMap"
                        ></l-tile-layer>
                        
                        <!-- Shop Marker (Only if not in transit or no GPS) -->
                        <l-marker v-if="!currentLocation" :lat-lng="shopLocation">
                            <l-tooltip>Notre Boutique (Départ)</l-tooltip>
                        </l-marker>

                        <!-- Livreur Live Marker -->
                        <l-marker v-if="currentLocation" :lat-lng="currentLocation">
                            <l-icon :icon-size="[32, 32]" :icon-anchor="[16, 16]" class-name="live-marker">
                                <i class='bx bxs-truck' style="color: var(--primary); font-size: 32px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                            </l-icon>
                            <l-tooltip>Votre position actuelle</l-tooltip>
                        </l-marker>

                        <!-- Client Location Marker -->
                        <l-marker :lat-lng="[deliveryLocation.lat, deliveryLocation.lng]">
                            <l-tooltip>Client (Arrivée)</l-tooltip>
                        </l-marker>

                        <!-- Trajet Line (Follows roads live) -->
                        <l-polyline 
                           :lat-lngs="routeCoordinates.length > 0 ? routeCoordinates : [currentLocation || shopLocation, [deliveryLocation.lat, deliveryLocation.lng]]" 
                           color="#FF5722" 
                           :weight="4" 
                           dashArray="5, 10"
                        ></l-polyline>
                    </l-map>
                 </div>
            </template>
            <template v-else>
                 <div class="empty">
                    <p>Aucune coordonnée de livraison disponible pour cette commande.</p>
                 </div>
            </template>
        </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useOrderStore } from '@/stores/orders';
import { getRoute } from '@/services/routing';

import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPolyline, LTooltip, LIcon } from "@vue-leaflet/vue-leaflet";
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

const route = useRoute();
const orderStore = useOrderStore();

const loading = ref(true);
const updating = ref(false);
const selectedOrder = ref(null);
const orderId = ref(route.params.id);

const zoom = ref(13);
const shopLocation = ref([4.038026, 9.741443]); // coord fixe boutique
const currentLocation = ref(null);
const routeCoordinates = ref([]);
const watchPositionId = ref(null);

const fetchRoadRoute = async () => {
    const start = currentLocation.value || shopLocation.value;
    if (!start || !deliveryLocation.value) return;

    const data = await getRoute(start, [deliveryLocation.value.lat, deliveryLocation.value.lng]);
    if (data) {
        routeCoordinates.value = data.coordinates;
    } else {
        // Fallback to straight line
        routeCoordinates.value = [start, [deliveryLocation.value.lat, deliveryLocation.value.lng]];
    }
};

// Update route when position changes
watch([currentLocation, deliveryLocation], () => {
    fetchRoadRoute();
}, { immediate: true });

const mapCenter = computed(() => {
    if (currentLocation.value && deliveryLocation.value) {
        return currentLocation.value;
    }
    if (deliveryLocation.value) {
        const midLat = (shopLocation.value[0] + deliveryLocation.value.lat) / 2;
        const midLng = (shopLocation.value[1] + deliveryLocation.value.lng) / 2;
        return [midLat, midLng];
    }
    return shopLocation.value;
});

const deliveryLocation = computed(() => {
    if (!selectedOrder.value || !selectedOrder.value.delivery_location) return null;
    try {
        const loc = selectedOrder.value.delivery_location;
        return typeof loc === 'string' ? JSON.parse(loc) : loc;
    } catch (e) {
        return null;
    }
});

// Group related items for bulk status update
const relatedItems = computed(() => {
    if (!selectedOrder.value) return [];
    
    const baseOrder = selectedOrder.value;
    const baseTime = new Date(baseOrder.created_at.replace(' ', 'T')).getTime();
    
    return orderStore.orders.filter(o => 
        o.user_id == baseOrder.user_id &&
        Boolean(o.delivery) === Boolean(baseOrder.delivery) &&
        Math.abs(new Date(o.created_at.replace(' ', 'T')).getTime() - baseTime) <= 120000
    );
});

const startTracking = () => {
    if (!navigator.geolocation) {
        console.error("La géolocalisation n'est pas supportée par votre navigateur.");
        return;
    }

    watchPositionId.value = navigator.geolocation.watchPosition(
        (position) => {
            currentLocation.value = [position.coords.latitude, position.coords.longitude];
        },
        (error) => {
            console.error("Erreur de géolocalisation:", error);
        },
        {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        }
    );
};

const stopTracking = () => {
    if (watchPositionId.value !== null) {
        navigator.geolocation.clearWatch(watchPositionId.value);
        watchPositionId.value = null;
    }
};

watch(() => selectedOrder.value?.status, (newStatus) => {
    if (newStatus === 'in_transit') {
        startTracking();
    } else {
        stopTracking();
    }
}, { immediate: true });

onMounted(async () => {
    loading.value = true;
    if (orderStore.orders.length === 0) {
        await orderStore.fetchOrders();
    }
    let found = orderStore.orders.find(o => o.id === orderId.value);
    
    if (found) {
        selectedOrder.value = found;
    }
    loading.value = false;
});

onUnmounted(() => {
    stopTracking();
});

const handleStatusUpdate = async (newStatus) => {
    if (updating.value) return;
    updating.value = true;
    try {
        const ids = relatedItems.value.map(o => o.id);
        await orderStore.updateOrdersStatusBulk(ids, newStatus);
        
        const fresh = orderStore.orders.find(o => o.id === orderId.value);
        if (fresh) {
            selectedOrder.value = fresh;
        }
    } finally {
        updating.value = false;
    }
};

const formatStatus = (status) => {
    const statuses = {
        pending: 'En attente',
        paid: 'Payée',
        in_transit: 'En cours',
        arrived: 'Arrivée',
        delivered: 'Livrée',
        cancelled: 'Annulée'
    };
    return statuses[status] || status;
};
</script>

<style scoped>
.livreur-details-page {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.page-header {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.btn-back {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: none;
    border: none;
    color: var(--primary);
    font-weight: 600;
    cursor: pointer;
    width: fit-content;
    padding: 0;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.btn-back i {
    font-size: 1.2rem;
}

.header-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.btn-action {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    font-size: 0.95rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.btn-start {
    background-color: var(--primary);
    color: white;
}

.btn-complete {
    background-color: #2E7D32;
    color: white;
}

.btn-action:hover:not(:disabled) {
    filter: brightness(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.btn-action:disabled {
    opacity: 0.7;
    cursor: wait;
}

.delivery-complete-badge {
    background-color: rgba(46, 125, 50, 0.1);
    color: #2E7D32;
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid rgba(46, 125, 50, 0.3);
}

.delivery-complete-badge i {
    font-size: 1.2rem;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
    color: var(--text);
}

.page-header p {
    color: #888;
    margin: 0;
}

.loading, .empty {
    text-align: center;
    padding: 4rem;
    color: #888;
    background-color: var(--surface);
    border-radius: 12px;
    border: 1px dashed var(--border);
}

.map-section {
    width: 100%;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.map-section h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    color: var(--text);
}

.distance-info {
    color: var(--primary);
    margin-bottom: 1rem;
}

.map-container {
    height: 600px;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid var(--border);
}

@media (max-width: 900px) {
    .map-container {
        height: 400px;
    }
}
</style>
