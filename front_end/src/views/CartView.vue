<template>
  <div class="cart-page">
    <header class="page-header">
      <div class="header-left">
        <h1>Mon Panier</h1>
        <p>{{ cartStore.itemCount }} article(s) dans votre panier</p>
      </div>
      <router-link v-if="cartStore.items.length > 0" to="/client/checkout" class="btn-checkout-header">
        Continuer vers le paiement <i class='bx bx-right-arrow-alt'></i>
      </router-link>
    </header>

    <div v-if="cartStore.items.length === 0" class="empty">
        <i class='bx bx-cart'></i>
        <p>Votre panier est vide.</p>
        <router-link to="/products" class="btn-primary">Faire mes achats</router-link>
    </div>

    <div v-else class="cart-container">
        <div class="cart-items">
            <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
                <img :src="item.image ? (item.image.startsWith('http') ? item.image : `/storage/${item.image}`) : 'https://placehold.co/100x100?text=Produit'" :alt="item.name" />
                <div class="cart-item__info">
                    <h4>{{ item.name }} ({{(formatPrice(item.price))}} FCFA / Unité)</h4>
                    <p>{{ formatPrice(item.price * item.quantity) }} FCFA</p>
                </div>
                <div class="cart-item__quantity">
                    <span>Quantité: <strong>{{ item.quantity }}</strong></span>
                </div>
                <button class="cart-item__remove" @click="cartStore.removeFromCart(item.id)">
                    <i class='bx bx-trash'></i>
                </button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { useCartStore } from '@/stores/cart';

const cartStore = useCartStore();
const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};
</script>

<style scoped>
.cart-page {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 3rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1.5rem;
}

.header-left h1 {
    margin: 0;
    font-size: 2rem;
}

.header-left p {
    color: #888;
    margin: 0.5rem 0 0;
}

.btn-checkout-header {
    background-color: var(--primary);
    color: #FFFFFF;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.cart-container {
    width: 100%;
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
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.cart-item__remove {
    background: none;
    border: none;
    color: #ff4d4d;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

.empty {
    text-align: center;
    padding: 6rem;
    background-color: var(--surface);
    border: 1px dashed var(--border);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.empty i {
    font-size: 4rem;
    color: var(--border);
}

.btn-primary {
    background-color: var(--primary);
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
}
</style>
