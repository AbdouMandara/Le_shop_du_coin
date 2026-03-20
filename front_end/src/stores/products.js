import { defineStore } from 'pinia';
import api from '@/services/api';
import { useAuthStore } from './auth';

export const useProductStore = defineStore('products', {
    state: () => ({
        products: [],
        categories: [],
        currentProduct: null,
        loading: false,
    }),
    actions: {
        _getPrefix() {
            const auth = useAuthStore();
            if (auth.isAuthenticated) {
                if (auth.isAdmin) return '/admin';
                if (auth.isLivreur) return '/livreur';
                if (auth.isUser) return '/client';
            }
            return '';
        },
        async fetchProducts() {
            this.loading = true;
            try {
                const prefix = this._getPrefix();
                const response = await api.get(`${prefix}/products`);
                this.products = response.data.data;
            } finally {
                this.loading = false;
            }
        },
        async fetchCategories() {
            const prefix = this._getPrefix();
            const response = await api.get(`${prefix}/categories`);
            this.categories = response.data;
        },
        async fetchProduct(id) {
            const prefix = this._getPrefix();
            const response = await api.get(`${prefix}/products/${id}`);
            this.currentProduct = response.data;
        }
    }
});
