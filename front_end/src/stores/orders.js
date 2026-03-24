import { defineStore } from 'pinia';
import api from '@/services/api';
import { useAuthStore } from './auth';

export const useOrderStore = defineStore('orders', {
    state: () => ({
        orders: [],
        loading: false,
    }),
    actions: {
        getPrefix() {
            const authStore = useAuthStore();
            if (authStore.isAdmin) return '/admin';
            if (authStore.isLivreur) return '/livreur';
            if (authStore.isUser) return '/client';
            return '';
        },
        async fetchOrders() {
            this.loading = true;
            try {
                const prefix = this.getPrefix();
                const response = await api.get(`${prefix}/orders`);
                this.orders = response.data.data; // Je mets response.data.data car coté laravel j'ai utilisé les collection dans les resources
            } finally {
                this.loading = false;
            }
        },
        async placeOrder(productId) {
            const prefix = this.getPrefix();
            await api.post(`${prefix}/orders`, { product_id: productId });
            await this.fetchOrders();
        },
        async updateOrderStatus(orderId, status) {
            const prefix = this.getPrefix();
            await api.patch(`${prefix}/orders/${orderId}`, { status });
            await this.fetchOrders();
        }
    }
});
