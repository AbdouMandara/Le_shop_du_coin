import { defineStore } from 'pinia';
import api from '@/services/api';
import { useAuthStore } from './auth';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: JSON.parse(localStorage.getItem('cart') || '[]'),
    }),
    getters: {
        totalPrice: (state) => state.items.reduce((acc, item) => acc + item.price * item.quantity, 0),
        itemCount: (state) => state.items.length,
    },
    actions: {
        addToCart(product) {
            const existing = this.items.find(i => i.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({ ...product, quantity: 1 });
            }
            this.saveCart();
        },
        removeFromCart(id) {
            this.items = this.items.filter(i => i.id !== id);
            this.saveCart();
        },
        saveCart() {
            localStorage.setItem('cart', JSON.stringify(this.items));
        }
    }
});
