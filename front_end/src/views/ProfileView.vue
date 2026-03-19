<template>
  <div class="profile-page">
    <header class="page-header">
      <h1>Mon Profil</h1>
    </header>

    <div class="profile-container">
      <section class="profile-card info-card">
        <h3>Informations Personnelles</h3>
        <div class="user-details">
            <div class="user-avatar">
                <i class='bx bx-user-circle'></i>
            </div>
            <div class="user-text">
                <p><strong>Nom:</strong> {{ authStore.user?.name }}</p>
                <p><strong>Email:</strong> {{ authStore.user?.email }}</p>
                <p><strong>Rôle:</strong> {{ authStore.user?.role?.label }}</p>
            </div>
        </div>
      </section>

      <section class="profile-card orders-card">
        <h3>Historique des Commandes</h3>
        <div v-if="orderStore.loading" class="loading">Chargement...</div>
        <div v-else-if="orderStore.orders.length === 0" class="empty-orders">
            Vous n'avez pas encore passé de commande.
        </div>
        <div v-else class="order-list">
            <div v-for="order in orderStore.orders" :key="order.id" class="order-item">
                <div class="order-info">
                    <p class="order-id">Commande #{{ order.id.substring(0, 8) }}</p>
                    <p class="order-date">{{ formatDate(order.created_at) }}</p>
                </div>
                <div class="order-product">
                    {{ order.product?.name }} - {{ order.product?.price }} FCFA
                </div>
                <div class="order-status" :class="order.status">
                    {{ order.status }}
                </div>
                <div class="order-download">
                    <a :href="'http://localhost:8000/api/orders/' + order.id + '/invoice'" target="_blank" class="btn-invoice">
                        <i class='bx bxs-file-pdf'></i>
                    </a>
                </div>
            </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useOrderStore } from '@/stores/orders';

const authStore = useAuthStore();
const orderStore = useOrderStore();

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

onMounted(() => {
    orderStore.fetchOrders();
});
</script>

<style scoped>
.profile-page {
    padding: 2rem;
}

.profile-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.profile-card {
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2rem;
}

.user-details {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.user-avatar i {
    font-size: 5rem;
    color: var(--primary);
}

.user-text p {
    margin: 0.5rem 0;
    font-size: 1.1rem;
}

.order-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
}

.order-id {
    font-weight: 700;
    margin: 0;
}

.order-date {
    font-size: 0.85rem;
    color: #888;
    margin: 0;
}

.order-product {
    flex: 1;
    margin: 0 2rem;
}

.order-status {
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
}

.order-status.pending { background-color: #FFF3E0; color: #E65100; }
.order-status.paid { background-color: #E8F5E9; color: #2E7D32; }
.order-status.delivered { background-color: #E3F2FD; color: #1565C0; }

.btn-invoice {
    color: #ff4d4d;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    transition: transform 0.2s;
}

.btn-invoice:hover {
    transform: scale(1.1);
}

[data-theme='dark'] .order-status.pending { background-color: #332B1A; color: #FFB74D; }
[data-theme='dark'] .order-status.paid { background-color: #1B2E1C; color: #81C784; }
</style>
