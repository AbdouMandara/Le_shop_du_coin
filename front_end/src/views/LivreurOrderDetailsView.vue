<template>
  <div class="livreur-details-page">
    <header class="page-header">
      <button class="btn-back" @click="$router.back()">
          <i class='bx bx-arrow-back'></i> Retour
      </button>
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
        
        <!-- Informations texte -->
        <div class="info-section">
            <div class="info-card">
              <h3>Produit commandé</h3>
              <div class="detail-row">
                 <div style="display:flex; align-items:center; gap:0.5rem">
                    <i class='bx bx-package'></i>
                    <span>{{ selectedOrder.product?.name }}</span>
                 </div>
                 <strong>{{ selectedOrder.product?.price }} FCFA</strong>
              </div>
            </div>

            <div class="info-card">
              <h3>Client</h3>
              <div class="detail-row">
                 <span>Nom :</span>
                 <strong>{{ selectedOrder.user?.name }}</strong>
              </div>
              <div class="detail-row">
                 <span>Contact :</span>
                 <strong>{{ selectedOrder.user?.email }}</strong>
              </div>
            </div>

            <div class="info-card">
              <h3>Statut</h3>
              <div class="detail-row">
                 <span>État actuel :</span>
                 <strong class="status-badge" :class="selectedOrder.status">{{ formatStatus(selectedOrder.status) }}</strong>
              </div>
            </div>
            
            <div class="actions-card" v-if="selectedOrder.status === 'paid' || selectedOrder.status === 'in_transit'">
                <button v-if="selectedOrder.status === 'paid'" @click="updateStatus(selectedOrder.id, 'in_transit')" class="btn-transit">
                    Prendre en compte
                </button>
                <button v-if="selectedOrder.status === 'in_transit'" @click="updateStatus(selectedOrder.id, 'delivered')" class="btn-deliver">
                    Confirmer la livraison
                </button>
            </div>
        </div>

        <!-- Carte de livraison -->
        <div class="map-section">
            <h3>Lieu de livraison</h3>
            <template v-if="deliveryLocation">
                 <p v-if="deliveryLocation.distanceKm" class="distance-info">
                     Distance estimée : <strong>{{ deliveryLocation.distanceKm }} km</strong>
                 </p>
                 <div class="map-container">
                    <l-map ref="map" v-model:zoom="zoom" :center="mapCenter">
                        <l-tile-layer
                            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                            layer-type="base"
                            name="OpenStreetMap"
                        ></l-tile-layer>
                        
                        <!-- Shop Marker -->
                        <l-marker :lat-lng="shopLocation">
                            <l-tooltip>Notre Boutique (Départ)</l-tooltip>
                        </l-marker>

                        <!-- Client Location Marker -->
                        <l-marker :lat-lng="[deliveryLocation.lat, deliveryLocation.lng]">
                            <l-tooltip>Client (Arrivée)</l-tooltip>
                        </l-marker>

                        <!-- Trajet Line -->
                        <l-polyline :lat-lngs="[shopLocation, [deliveryLocation.lat, deliveryLocation.lng]]" color="#FF5722" :weight="3" dashArray="10, 10"></l-polyline>
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
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useOrderStore } from '@/stores/orders';

import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPolyline, LTooltip } from "@vue-leaflet/vue-leaflet";
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
const selectedOrder = ref(null);
const orderId = ref(route.params.id);

const zoom = ref(13);
const shopLocation = ref([4.038026, 9.741443]); // coord fixe boutique

const mapCenter = computed(() => {
    if (deliveryLocation.value) {
        // centrer entre les deux points : (lat1+lat2)/2, (lng1+lng2)/2
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

onMounted(async () => {
    loading.value = true;
    if (orderStore.orders.length === 0) {
        await orderStore.fetchOrders();
    }
    // Trouver la commande correspondant dans le store
    // Soit pour Livreur simple, soit dans les items de groupes pour OrdersView admin/livreur
    let found = orderStore.orders.find(o => o.id === orderId.value);
    
    if (found) {
        selectedOrder.value = found;
    }
    loading.value = false;
});

const updateStatus = async (id, status) => {
    await orderStore.updateOrderStatus(id, status);
    // Rafraichir l'objet dans la vue
    const fresh = orderStore.orders.find(o => o.id === orderId.value);
    if (fresh) {
        selectedOrder.value = fresh;
    }
};

const formatStatus = (status) => {
    const statuses = {
        pending: 'En attente',
        paid: 'Payée',
        in_transit: 'En cours',
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

.details-container {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}

.info-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.info-card h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    color: var(--text);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    background-color: var(--background);
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-bottom: 0.5rem;
}

.actions-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.map-section {
    flex: 2;
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
    height: 500px;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid var(--border);
}

.status-badge {
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.pending { background: #ECEFF1; color: #455A64; }
.status-badge.paid { background: #E8F5E9; color: #2E7D32; }
.status-badge.in_transit { background: #FFF3E0; color: #E65100; }
.status-badge.delivered { background: #E3F2FD; color: #1565C0; }
.status-badge.cancelled { background: #FFEBEE; color: #C62828; }

.btn-deliver, .btn-transit {
    width: 100%;
    color: #FFFFFF;
    border: none;
    padding: 1rem;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    font-size: 1rem;
}

.btn-deliver {
    background-color: var(--secondary);
}

.btn-transit {
    background-color: var(--primary);
}

@media (max-width: 900px) {
    .details-container {
        flex-direction: column;
    }
    
    .map-section, .info-section {
        width: 100%;
    }
}
</style>
