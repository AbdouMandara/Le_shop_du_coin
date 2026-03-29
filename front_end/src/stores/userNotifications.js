import { defineStore } from 'pinia';
import api from '@/services/api';
import { useAuthStore } from './auth';

export const useUserNotificationsStore = defineStore('userNotifications', {
    state: () => ({
        notifications: [],
        loading: false,
    }),
    getters: {
        unreadCount: (state) => state.notifications.filter(n => !n.read_at).length,
    },
    actions: {
        async fetchNotifications() {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated || !authStore.isUser) return;

            this.loading = true;
            try {
                const response = await api.get('/client/notifications');
                this.notifications = response.data;
            } catch (err) {
                console.error('Erreur de chargement des notifications', err);
            } finally {
                this.loading = false;
            }
        },
        async markAsRead(id) {
            try {
                await api.patch(`/client/notifications/${id}/read`);
                const notif = this.notifications.find(n => n.id === id);
                if (notif) notif.read_at = new Date().toISOString();
            } catch (err) {
                console.error('Erreur lors du marquage de la notification', err);
            }
        }
    }
});
