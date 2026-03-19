<template>
  <div class="admin-page">
    <header class="page-header">
      <h1>Administration</h1>
      <div class="admin-tabs">
        <button :class="{ active: tab === 'products' }" @click="tab = 'products'">Produits</button>
        <button :class="{ active: tab === 'orders' }" @click="tab = 'orders'">Commandes</button>
      </div>
    </header>

    <div v-if="tab === 'products'" class="admin-content">
        <div class="content-header">
            <h3>Gestion des Produits</h3>
            <button class="btn-add">Ajouter un produit</button>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="p in productStore.products" :key="p.id">
                    <td>{{ p.name }}</td>
                    <td>{{ p.price }} FCFA</td>
                    <td>{{ p.quantity }}</td>
                    <td class="actions">
                        <button class="btn-icon"><i class='bx bx-edit'></i></button>
                        <button class="btn-icon delete"><i class='bx bx-trash'></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="tab === 'orders'" class="admin-content">
        <h3>Toutes les Commandes</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Produit</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="o in orderStore.orders" :key="o.id">
                    <td>{{ o.user?.name }}</td>
                    <td>{{ o.product?.name }}</td>
                    <td><span class="status-badge" :class="o.status">{{ o.status }}</span></td>
                    <td>
                        <select v-model="o.status" @change="orderStore.updateOrderStatus(o.id, o.status)">
                            <option value="pending">En attente</option>
                            <option value="paid">Payé</option>
                            <option value="delivered">Livré</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useProductStore } from '@/stores/products';
import { useOrderStore } from '@/stores/orders';

const productStore = useProductStore();
const orderStore = useOrderStore();
const tab = ref('products');

onMounted(() => {
    productStore.fetchProducts();
    orderStore.fetchOrders();
});
</script>

<style scoped>
.admin-page {
    padding: 2rem;
}

.admin-tabs {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.admin-tabs button {
    background: none;
    border: none;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
    font-weight: 600;
    color: #888;
    border-bottom: 3px solid transparent;
}

.admin-tabs button.active {
    color: var(--primary);
    border-bottom-color: var(--secondary);
}

.admin-content {
    margin-top: 2.5rem;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.btn-add {
    background-color: var(--secondary);
    color: #FFFFFF;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--surface);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border);
}

.admin-table th, .admin-table td {
    padding: 1rem 1.5rem;
    text-align: left;
}

.admin-table th {
    background-color: var(--neutral);
    font-weight: 700;
}

.admin-table tr:not(:last-child) {
    border-bottom: 1px solid var(--border);
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--primary);
}

.btn-icon.delete {
    color: #ff4d4d;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.pending { background: #FFF3E0; color: #E65100; }
.status-badge.paid { background: #E8F5E9; color: #2E7D32; }

select {
    padding: 0.4rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background-color: var(--background);
    color: var(--text);
}
</style>
