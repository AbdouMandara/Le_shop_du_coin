<template>
  <div class="cart-page">
    <header class="page-header">
        <h1>Mon Panier</h1>
        <p>{{ cartStore.itemCount }} article(s) dans votre panier</p>
    </header>

    <div v-if="cartStore.items.length === 0" class="empty">
        <i class='bx bx-cart'></i>
        <p>Votre panier est vide.</p>
        <router-link to="/products" class="btn-primary">Faire mes achats</router-link>
    </div>

    <div v-else class="cart-container">
        <div class="cart-items">
            <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
                <img :src="item.image || 'https://via.placeholder.com/100'" :alt="item.name" />
                <div class="cart-item__info">
                    <h4>{{ item.name }}</h4>
                    <p>{{ item.price }} FCFA</p>
                </div>
                <div class="cart-item__quantity">
                    <button @click="updateQty(item, -1)" :disabled="item.quantity <= 1">-</button>
                    <span>{{ item.quantity }}</span>
                    <button @click="updateQty(item, 1)">+</button>
                </div>
                <button class="cart-item__remove" @click="cartStore.removeFromCart(item.id)">
                    <i class='bx bx-trash'></i>
                </button>
            </div>
        </div>

        <div class="cart-summary">
            <h3>Résumé</h3>
            <div class="summary-row">
                <span>Sous-total</span>
                <span>{{ cartStore.totalPrice }} FCFA</span>
            </div>
            <div class="summary-row">
                <span>Livraison</span>
                <span class="free">Gratuit</span>
            </div>
            <hr />
            <div class="summary-row total">
                <span>Total</span>
                <span>{{ cartStore.totalPrice }} FCFA</span>
            </div>
            <button class="btn-checkout" @click="handleCheckout" :disabled="loading">
                <span v-if="!loading">Passer la commande</span>
                <i v-else class='bx bx-loader-alt bx-spin'></i>
            </button>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useOrderStore } from '@/stores/orders';
import { useRouter } from 'vue-router';

const cartStore = useCartStore();
const orderStore = useOrderStore();
const router = useRouter();
const loading = ref(false);

const updateQty = (item, diff) => {
    item.quantity += diff;
    cartStore.saveCart();
};

const handleCheckout = async () => {
    loading.value = true;
    try {
        for (const item of cartStore.items) {
            await orderStore.placeOrder(item.id);
        }
        cartStore.items = [];
        cartStore.saveCart();
        router.push({ name: 'profile' }); // Redirect to profile to see orders
    } catch (err) {
        alert('Erreur lors de la commande');
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.cart-page {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 3rem;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
}

.page-header p {
    color: #888;
}

.cart-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 3rem;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
}

.cart-item img {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    object-fit: cover;
}

.cart-item__info {
    flex: 1;
}

.cart-item__info h4 {
    margin: 0;
    font-size: 1.1rem;
}

.cart-item__info p {
    color: var(--secondary);
    font-weight: 700;
    margin-top: 0.5rem;
}

.cart-item__quantity {
    display: flex;
    align-items: center;
    gap: 1rem;
    background-color: var(--neutral);
    padding: 0.5rem;
    border-radius: 8px;
}

.cart-item__quantity button {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.25rem;
    padding: 0 0.5rem;
}

.cart-item__remove {
    background: none;
    border: none;
    color: #ff4d4d;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

.cart-summary {
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2rem;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.cart-summary h3 {
    margin-top: 0;
    margin-bottom: 2rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    color: #666;
}

.summary-row.total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
    margin-top: 1.5rem;
}

.free {
    color: #4CAF50;
    font-weight: 600;
}

.btn-checkout {
    width: 100%;
    margin-top: 2rem;
    background-color: var(--primary);
    color: #FFFFFF;
    border: none;
    padding: 1rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
}

.empty {
    text-align: center;
    padding: 6rem;
    background-color: var(--surface);
    border: 1px dashed var(--border);
    border-radius: 12px;
}
</style>
