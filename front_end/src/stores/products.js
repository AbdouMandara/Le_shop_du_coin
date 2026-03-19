import { defineStore } from 'pinia';
import api from '@/services/api';

export const useProductStore = defineStore('products', {
    state: () => ({
        products: [],
        categories: [],
        currentProduct: null,
        loading: false,
    }),
    actions: {
        async fetchProducts() {
            this.loading = true;
            try {
                const response = await api.get('/products');
                this.products = response.data.data;
            } finally {
                this.loading = false;
            }
        },
        async fetchCategories() {
            const response = await api.get('/categories');
            this.categories = response.data;
        },
        async fetchProduct(id) {
            const response = await api.get(`/products/${id}`);
            this.currentProduct = response.data;
        }
    }
});
