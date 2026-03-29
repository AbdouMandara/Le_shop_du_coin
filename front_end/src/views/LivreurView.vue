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
        <div v-else class="order-card" v-for="group in groupedOrders" :key="group.id">
            <div class="order-header">
                <h3>Commande #{{ String(group.items[0]?.id).substring(0, 8) }}</h3>
                <span class="status-badge" :class="group.status">{{ formatStatus(group.status) }}</span>
            </div>
            <div class="order-body">
                <p><strong>Client:</strong> {{ group.user?.name }}</p>
            </div>
            <div class="order-actions">
                <button @click="$router.push({ name: 'livreur-order-details', params: { id: group.items[0]?.id } })" class="btn-secondary" style="width: 100%">
                    <i class='bx bx-show'></i> Voir les détails
                </button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useOrderStore } from '@/stores/orders';


const orderStore = useOrderStore();

const groupedOrders = computed(() => {
    const groups = [];
    
    orderStore.orders.forEach(order => {
        const timeString = typeof order.created_at === 'string' ? order.created_at.replace(' ', 'T') : order.created_at;
        const orderTime = new Date(timeString).getTime();
        const userId = order.user?.id || order.user_id || 'guest';
        
        let foundGroup = groups.find(g => 
            g.user_id == userId && 
            Boolean(g.delivery) === Boolean(order.delivery) && 
            !isNaN(g.baseTime) && !isNaN(orderTime) &&
            Math.abs(g.baseTime - orderTime) <= 120000
        );
        
        if (!foundGroup) {
            foundGroup = {
                id: `group_${userId}_${orderTime}_${order.delivery}`, 
                baseTime: orderTime,
                user_id: userId,
                created_at: order.created_at,
                delivery: order.delivery,
                status: order.status,
                user: order.user,
                livreur: order.livreur,
                items: [],
                totalPrice: 0
            };
            groups.push(foundGroup);
        }
        
        foundGroup.items.push(order);
        if (order.product && order.product.price) {
            foundGroup.totalPrice += parseFloat(order.product.price);
        }
    });
    
    return groups.sort((a,b) => new Date(b.created_at) - new Date(a.created_at));
});

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
