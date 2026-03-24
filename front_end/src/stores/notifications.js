import { defineStore } from 'pinia';

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        notifications: [],
    }),
    actions: {
        addNotification(notification) {
            const id = Date.now();
            this.notifications.push({
                id,
                type: 'success', // success, error, warning, info
                duration: 3000,
                ...notification,
            });

            setTimeout(() => {
                this.removeNotification(id);
            }, notification.duration || 3000);
        },
        removeNotification(id) {
            this.notifications = this.notifications.filter((n) => n.id !== id);
        },
        success(message, duration = 3000) {
            this.addNotification({ type: 'success', message, duration });
        },
        error(message, duration = 4000) {
            this.addNotification({ type: 'error', message, duration });
        },
        warning(message, duration = 3000) {
            this.addNotification({ type: 'warning', message, duration });
        },
        info(message, duration = 3000) {
            this.addNotification({ type: 'info', message, duration });
        },
    },
});
