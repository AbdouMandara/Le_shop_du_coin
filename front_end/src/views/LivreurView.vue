<template>
  <div class="livreur-page">
    <header class="page-header">
      <h1>Espace Livreur</h1>
      <p>Gérez les livraisons en cours.</p>
    </header>

    <div class="orders-list">
        <div v-if="orderStore.loading" class="loading">Chargement...</div>
        <div v-else-if="orderStore.orders.length === 0" class="empty">
            Aucune commande à livrer pour le moment.
        </div>
        <div v-else class="order-card" v-for="order in orderStore.orders" :key="order.id">
            <div class="order-header">
                <h3>Commande #{{ order.id.substring(0, 8) }}</h3>
                <span class="status-badge" :class="order.status">{{ formatStatus(order.status) }}</span>
            </div>
            <div class="order-body">
                <p><strong>Client:</strong> {{ order.user?.name }}</p>
                <p><strong>Produit:</strong> {{ order.product?.name }}</p>
            </div>
            <div class="order-actions">
                <button @click="$router.push({ name: 'livreur-order-details', params: { id: order.id } })" class="btn-secondary" style="margin-bottom: 0.5rem; width: 100%">
                    <i class='bx bx-show'></i> Voir les détails
                </button>
                <button v-if="order.status === 'paid'" @click="updateStatus(order.id, 'in_transit')" class="btn-transit">
                    Prendre en compte
                </button>
                <button v-if="order.status === 'in_transit'" @click="updateStatus(order.id, 'delivered')" class="btn-deliver">
                    Confirmer la livraison
                </button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useOrderStore } from '@/stores/orders';

const orderStore = useOrderStore();

const updateStatus = async (id, status) => {
    await orderStore.updateOrderStatus(id, status);
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

onMounted(() => {
    orderStore.fetchOrders();
});
</script>

<style scoped>
.livreur-page {
    padding: 2rem;
}

.orders-list {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.paid { background: #E8F5E9; color: #2E7D32; }
.status-badge.in_transit { background: #FFF3E0; color: #E65100; }
.status-badge.delivered { background: #E3F2FD; color: #1565C0; }

.btn-deliver, .btn-transit {
    width: 100%;
    color: #FFFFFF;
    border: none;
    padding: 0.75rem;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
}

.btn-deliver {
    background-color: var(--secondary);
}

.btn-transit {
    background-color: var(--primary);
}

.btn-secondary {
    background-color: #FFFFFF;
    color: var(--text);
    border: 2px solid var(--border);
    padding: 0.75rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background-color: var(--neutral);
}

.btn-primary {
    background-color: var(--primary);
    color: #FFFFFF;
    border: none;
    padding: 0.75rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

</style>
