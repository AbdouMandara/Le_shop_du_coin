<template>
  <div class="orders-page">
    <header class="page-header">
      <h1>Commandes</h1>
      <p v-if="authStore.isUser">Vos commandes récentes</p>
      <p v-else>Gestion des commandes clients</p>
    </header>

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
import { onMounted } from 'vue';
import { useOrderStore } from '@/stores/orders';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const orderStore = useOrderStore();
const authStore = useAuthStore();

onMounted(async () => {
    await orderStore.fetchOrders();
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
    transition: transform 0.2s, box-shadow 0.2s;
}

.order-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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
    transition: border-color 0.2s;
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
    transition: all 0.2s;
}

.btn-download:hover {
    background-color: #e60000;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(230, 0, 0, 0.2);
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
