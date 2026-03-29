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
        async fetchOrders(params = {}) {
            this.loading = true;
            try {
                const prefix = this.getPrefix();
                const response = await api.get(`${prefix}/orders`, { params });
                this.orders = response.data.data;
            } finally {
                this.loading = false;
            }
        },
        async placeOrder(productId, delivery = false, deliveryLocation = null) {
            const prefix = this.getPrefix();
            const payload = { 
                product_id: productId, 
                delivery: delivery 
            };
            if (delivery && deliveryLocation) {
                payload.delivery_location = deliveryLocation;
            }
            await api.post(`${prefix}/orders`, payload);
            await this.fetchOrders();
        },
        async updateOrderStatus(orderId, status) {
            const prefix = this.getPrefix();
            await api.patch(`${prefix}/orders/${orderId}`, { status });
            await this.fetchOrders();
        },
        async updateOrdersStatusBulk(ids, status) {
            const prefix = this.getPrefix();
            await api.patch(`${prefix}/orders/bulk-status-update`, { ids, status });
            await this.fetchOrders();
        }
    }
});
