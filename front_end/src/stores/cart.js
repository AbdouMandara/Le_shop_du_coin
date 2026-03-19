import { defineStore } from 'pinia';
import api from '@/services/api';
import { useAuthStore } from './auth';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: JSON.parse(localStorage.getItem('cart') || '[]'),
        favorites: [],
    }),
    getters: {
        totalPrice: (state) => state.items.reduce((acc, item) => acc + item.price * item.quantity, 0),
        itemCount: (state) => state.items.length,
    },
    actions: {
        getPrefix() {
            const authStore = useAuthStore();
            if (authStore.isUser) return '/client';
            return '';
        },
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
        },
        async fetchFavorites() {
            const prefix = this.getPrefix();
            const response = await api.get(`${prefix}/favorites`);
            this.favorites = response.data;
        },
        async toggleFavorite(productId) {
            const prefix = this.getPrefix();
            const existing = this.favorites.find(f => f.product_id === productId);
            if (existing) {
                await api.delete(`${prefix}/favorites/${existing.id}`);
                this.favorites = this.favorites.filter(f => f.id !== existing.id);
            } else {
                const response = await api.post(`${prefix}/favorites`, { product_id: productId });
                this.favorites.push(response.data);
            }
        }
    }
});
