import { defineStore } from 'pinia';
import api from '@/services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: false,
        error: null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.user,
        isAdmin: (state) => {
            const role = state.user?.role;
            return typeof role === 'string' ? role === 'admin' : role?.label === 'admin';
        },
        isUser: (state) => {
            const role = state.user?.role;
            return typeof role === 'string' ? role === 'user' : role?.label === 'user';
        },
        isLivreur: (state) => {
            const role = state.user?.role;
            return typeof role === 'string' ? role === 'livreur' : role?.label === 'livreur';
        },
    },
    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                await api.get('http://localhost:8000/sanctum/csrf-cookie');
                const response = await api.post('/login', credentials);
                await this.fetchUser();
                localStorage.setItem('isLoggedIn', 'true');
            } catch (err) {
                console.error('Auth Error:', err.response || err);
                this.error = err.response?.data?.message || 'Login failed';
                throw err;
            } finally {
                this.loading = false;
            }
        },
        async fetchUser() {
            try {
                const response = await api.get('/user');
                this.user = response.data;
                return response.data;
            } catch (error) {
                this.user = null;
                localStorage.removeItem('isLoggedIn');
            }
        },
        async logout() {
            await api.post('/logout');
            this.user = null;
            localStorage.removeItem('user');
            localStorage.removeItem('isLoggedIn');
        }
    }
});