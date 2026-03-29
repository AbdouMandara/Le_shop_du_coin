<template>
  <div class="livreur-page">
    <header class="page-header">
      <div class="header-main">
          <h1>Espace Livreur</h1>
          <p>Gérez les livraisons en cours.</p>
      </div>

      <div class="filter-section">
          <div class="filter-dropdown-wrapper" ref="filterMenuRef">
              <button class="action-btn" @click.stop="showFilterModal = !showFilterModal">
                  <i class='bx bx-filter-alt'></i> Filtres
                  <span v-if="selectedStatus" class="filter-badge">1</span>
              </button>
              
              <div v-if="showFilterModal" class="filter-dropdown" @click.stop>
                  <div class="filter-dropdown-header">
                      <h3>Filtres détaillés</h3>
                  </div>
                  
                  <div class="filter-dropdown-body">
                      <div class="filter-group">
                          <label>Statut de livraison</label>
                          <select :value="selectedStatus" @change="setStatus($event.target.value)" class="filter-input">
                              <option v-for="tab in tabs" :key="tab.value" :value="tab.value">
                                  {{ tab.label }}
                              </option>
                          </select>
                      </div>
                  </div>

                  <div class="filter-dropdown-footer" v-if="selectedStatus">
                      <button @click="resetFilters" class="btn-reset-filters">
                          <i class='bx bx-refresh'></i> Réinitialiser
                      </button>
                  </div>
              </div>
          </div>
      </div>
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
import { onMounted, onUnmounted, computed, ref } from 'vue';
import { useOrderStore } from '@/stores/orders';

const orderStore = useOrderStore();
const selectedStatus = ref('');
const showFilterModal = ref(false);
const filterMenuRef = ref(null);

const tabs = [
    { label: 'Tous les statuts', value: '' },
    { label: 'À livrer (Payée)', value: 'paid' },
    { label: 'En cours de livraison', value: 'in_transit' },
    { label: 'Livrée (Terminée)', value: 'delivered' },
];

const setStatus = (status) => {
    selectedStatus.value = status;
    orderStore.fetchOrders({ status: selectedStatus.value });
    // Optionnel : fermer le menu après sélection
    // showFilterModal.value = false;
};

const resetFilters = () => {
    selectedStatus.value = '';
    orderStore.fetchOrders();
    showFilterModal.value = false;
};

const closeFilterMenu = (e) => {
    if (filterMenuRef.value && !filterMenuRef.value.contains(e.target)) {
        showFilterModal.value = false;
    }
};

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
    document.addEventListener('click', closeFilterMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeFilterMenu);
});
</script>

<style scoped>
.livreur-page {
    padding: 2rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.filter-dropdown-wrapper {
    position: relative;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    border: 1px solid var(--border);
    border-radius: 12px;
    background-color: var(--surface);
    color: var(--text);
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
    font-family: 'Cal Sans', sans-serif;
}

.action-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.filter-badge {
    background-color: var(--primary);
    color: white;
    font-size: 0.7rem;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.filter-dropdown {
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    width: 280px;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    z-index: 100;
}

.filter-dropdown-header {
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border);
}

.filter-dropdown-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-family: 'Cal Sans', sans-serif;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #888;
}

.filter-input {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background-color: var(--background);
    color: var(--text);
    font-size: 0.95rem;
    width: 100%;
    outline: none;
}

.filter-dropdown-footer {
    margin-top: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.btn-reset-filters {
    width: 100%;
    padding: 0.75rem;
    border: none;
    background: none;
    color: var(--primary);
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    transition: background 0.2s;
    border-radius: 8px;
}

.btn-reset-filters:hover {
    background-color: var(--neutral);
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
