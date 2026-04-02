import { defineStore } from 'pinia';
import api from '@/services/api';
import { useAuthStore } from './auth';

export const useProductStore = defineStore('products', {
    state: () => ({
        products: [],
        promotionalProducts: [],
        categories: [],
        currentProduct: null,
        loading: false,
        loadingPromotions: false,
        searchQuery: '',
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
                const url = prefix ? `${prefix}/products` : '/products';
                const response = await api.get(url);
                this.products = response.data.data;
            } catch (error) {
                console.error("Error fetching products:", error);
                if (this._getPrefix() !== '') {
                    try {
                        const response = await api.get('/products');
                        this.products = response.data.data;
                    } catch (e) {
                        console.error("Fallback fetch failed:", e);
                    }
                }
            } finally {
                this.loading = false;
            }
        },
        async fetchPromotionalProducts() {
            this.loadingPromotions = true;
            try {
                const response = await api.get('/products/promotional');
                this.promotionalProducts = response.data.data;
            } catch (error) {
                console.error("Error fetching promotional products:", error);
            } finally {
                this.loadingPromotions = false;
            }
        },
        async fetchCategories() {
            const prefix = this._getPrefix();
            const response = await api.get(`${prefix}/categories`);
            this.categories = response.data.data;
        },
        async fetchProduct(id) {
            const prefix = this._getPrefix();
            const response = await api.get(`${prefix}/products/${id}`);
            this.currentProduct = response.data;
        },
        async addProduct(productData) {
            const prefix = this._getPrefix();
            const formData = new FormData();
            Object.keys(productData).forEach(key => {
                if (productData[key] !== null) {
                    formData.append(key, productData[key]);
                }
            });

            const response = await api.post(`${prefix}/products`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            const newProduct = response.data.data || response.data;
            this.products.unshift(newProduct);
        },
        async updateProduct(id, productData) {
            const prefix = this._getPrefix();
            const formData = new FormData();
            Object.keys(productData).forEach(key => {
                if (productData[key] !== null) {
                    formData.append(key, productData[key]);
                }
            });
            // Workaround pour PUT avec multipart/form-data
            formData.append('_method', 'PUT');

            const response = await api.post(`${prefix}/products/${id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            const index = this.products.findIndex(p => p && p.id === id);
            if (index !== -1) {
                this.products[index] = response.data.data || response.data;
            }
        },
        async deleteProduct(id) {
            const prefix = this._getPrefix();
            await api.delete(`${prefix}/products/${id}`);
            this.products = this.products.filter(p => p.id !== id);
        }
    }
})
