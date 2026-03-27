<template>
  <div class="orders-page">
    <header class="page-header">
      <h1>Commandes</h1>
      <p v-if="authStore.isUser">Vos commandes récentes</p>
      <p v-else>Gestion des commandes clients</p>
    </header>

    <div v-if="!authStore.isUser" class="filters-section">
      <div class="filter-actions">
        <div class="filter-dropdown-wrapper" ref="filterMenuRef">
          <button class="action-btn" @click.stop="showFilterModal = !showFilterModal">
            <i class='bx bx-filter-alt'></i> Filtres
            <span v-if="activeFiltersCount > 0" class="filter-count">{{ activeFiltersCount }}</span>
          </button>
          
          <div v-if="showFilterModal" class="filter-dropdown" @click.stop>
            <div class="filter-dropdown-header">
              <h3>Filtres détaillés</h3>
            </div>
            
            <div class="filter-dropdown-body">
              <div class="filter-group">
                <label>Statut de commande</label>
                <select v-model="filters.status" class="filter-input">
                  <option value="">Tous les statuts</option>
                  <option value="pending">En attente</option>
                  <option value="paid">Payée</option>
                  <option value="delivered">Livrée</option>
                  <option value="canceled">Annulée</option>
                </select>
              </div>
              <div class="filter-group">
                <label>Date de création</label>
                <input type="date" v-model="filters.date" class="filter-input">
              </div>
            </div>

            <div class="filter-dropdown-footer" v-if="activeFiltersCount > 0">
              <button @click="resetFilters" class="btn-clear">
                <i class='bx bx-refresh'></i> Réinitialiser
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="orderStore.loading" class="loading">
      <i class='bx bx-loader-alt bx-spin'></i> Chargement...
    </div>
    
    <div v-else-if="orderStore.orders.length === 0" class="empty">
      <i class='bx bx-package'></i>
      <p>Aucune commande trouvée.</p>
    </div>

    <div v-else class="orders-list">
      <div v-for="order in orderStore.orders" :key="order.id" class="order-card">
        <div class="order-info">
          <div class="order-header">
             <span class="order-id">Commande #{{ order.id }}</span>
             <span class="order-date">{{ formatDate(order.created_at) }}</span>
          </div>
          <div class="order-product">
             <h4>{{ order.product?.name || 'Produit inconnu' }}</h4>
             <p v-if="!authStore.isUser && order.user">Client: {{ order.user.name }}</p>
             <p v-if="order.delivery">Avec livraison</p>
          </div>
        </div>
        
        <div class="order-actions">
          <div class="status-container">
            <span v-if="authStore.isUser" :class="['status-badge', order.status]">
               {{ formatStatus(order.status) }}
            </span>
            <select 
               v-else 
               v-model="order.status" 
               @change="updateStatus(order.id, order.status)"
               class="status-select"
            >
               <option value="pending">En attente</option>
               <option value="paid">Payée</option>
               <option value="delivered">Livrée</option>
               <option value="canceled">Annulée</option>
            </select>
          </div>
          
          <button 
             v-if="authStore.isUser" 
             @click="downloadInvoice(order.id)" 
             class="btn-download"
             title="Télécharger la facture"
          >
             <i class='bx bxs-file-pdf'></i> PDF
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, watch, ref, computed } from 'vue';
import { useOrderStore } from '@/stores/orders';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const orderStore = useOrderStore();
const authStore = useAuthStore();

const filters = reactive({
    status: '',
    date: ''
});

const showFilterModal = ref(false);
const filterMenuRef = ref(null);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.status) count++;
    if (filters.date) count++;
    return count;
});

const resetFilters = () => {
    filters.status = '';
    filters.date = '';
};

const closeFilterMenu = (e) => {
    if (filterMenuRef.value && !filterMenuRef.value.contains(e.target)) {
        showFilterModal.value = false;
    }
};

watch(filters, () => {
    orderStore.fetchOrders(filters);
});

onMounted(async () => {
    await orderStore.fetchOrders();
    document.addEventListener('click', closeFilterMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeFilterMenu);
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute:'2-digit' }).format(date);
};

const formatStatus = (status) => {
    const map = {
        pending: 'En attente',
        paid: 'Payée',
        delivered: 'Livrée',
        canceled: 'Annulée'
    };
    return map[status] || status;
};

const updateStatus = async (orderId, newStatus) => {
    try {
        await orderStore.updateOrderStatus(orderId, newStatus);
    } catch (err) {
        alert('Erreur lors de la mise à jour du statut');
    }
};

const downloadInvoice = async (orderId) => {
    try {
        const response = await api.get(`/client/orders/${orderId}/invoice`, { responseType: 'blob' });
        const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `facture-${orderId.substring(0,8)}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (err) {
        alert('Impossible de télécharger la facture.');
    }
};
</script>

<style scoped>
.orders-page {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
    color: var(--text);
}

.page-header p {
    color: #888;
    margin-top: 0.5rem;
}

.loading, .empty {
    text-align: center;
    padding: 4rem;
    color: #888;
    font-size: 1.2rem;
    background-color: var(--surface);
    border-radius: 12px;
    border: 1px dashed var(--border);
}

.filters-section {
    margin-bottom: 2rem;
}

.filter-actions {
    display: flex;
    justify-content: flex-end;
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.action-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.filter-count {
    background-color: var(--primary);
    color: white;
    font-size: 0.75rem;
    width: 20px;
    height: 20px;
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
    font-size: 1rem;
    color: var(--text);
    font-weight: 700;
}

.filter-dropdown-body {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text);
    opacity: 0.7;
}

.filter-input {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 10px;
    background-color: var(--background);
    color: var(--text);
    outline: none;
    font-size: 0.95rem;
}

.filter-input:focus {
    border-color: var(--primary);
    background-color: var(--surface);
}

.filter-dropdown-footer {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.btn-clear {
    width: 100%;
    padding: 0.75rem;
    background: var(--neutral);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-clear:hover {
    background-color: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.empty i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--border);
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
}

.order-card:hover {
    border-color: var(--primary);
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.order-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.order-id {
    font-weight: 700;
    color: var(--primary);
    background-color: var(--neutral);
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.85rem;
}

.order-date {
    color: #888;
    font-size: 0.9rem;
}

.order-product h4 {
    margin: 0 0 0.25rem;
    font-size: 1.1rem;
    color: var(--text);
}

.order-product p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.order-actions {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-badge.pending { background-color: #fff3cd; color: #856404; }
.status-badge.paid { background-color: #d4edda; color: #155724; }
.status-badge.delivered { background-color: #d1ecf1; color: #0c5460; }
.status-badge.canceled { background-color: #f8d7da; color: #721c24; }

.status-select {
    padding: 0.6rem 1rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background-color: var(--background);
    color: var(--text);
    font-weight: 600;
    cursor: pointer;
    outline: none;
}

.status-select:focus {
    border-color: var(--primary);
}

.btn-download {
    background-color: #ff4d4d;
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-download:hover {
    background-color: #e60000;
}

.btn-download i {
    font-size: 1.2rem;
}

@media (max-width: 768px) {
  .order-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 1.5rem;
  }
  .order-actions {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
