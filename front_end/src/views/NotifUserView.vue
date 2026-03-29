<template>
  <div class="notifications-page">
    <header class="page-header">
      <h1>Mes Notifications</h1>
      <p>Retrouvez vos alertes et l'état de vos commandes.</p>
    </header>

    <div v-if="notifStore.loading" class="loading">
      <i class='bx bx-loader-alt bx-spin'></i> Chargement...
    </div>

    <div v-else-if="notifStore.notifications.length === 0" class="empty">
      <i class='bx bx-bell-off'></i>
      <p>Aucune notification pour le moment.</p>
    </div>

    <div v-else class="notifications-list">
      <div 
        v-for="notif in notifStore.notifications" 
        :key="notif.id" 
        class="notification-card"
        :class="{ 'unread': !notif.read_at }"
      >
        <div class="notification-icon">
          <i class='bx bxs-bell-ring'></i>
        </div>
        <div class="notification-content">
          <p class="message">{{ notif.message }}</p>
          <span class="date">{{ formatDate(notif.created_at) }}</span>
        </div>
        <div class="notification-actions">
          <button v-if="!notif.read_at" @click="notifStore.markAsRead(notif.id)" class="btn-read" title="Marquer comme lu">
            <i class='bx bx-check-circle'></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useUserNotificationsStore } from '@/stores/userNotifications';

const notifStore = useUserNotificationsStore();

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute:'2-digit'
    }).format(date);
};

onMounted(() => {
    notifStore.fetchNotifications();
});
</script>

<style scoped>
.notifications-page {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
    color: var(--text);
}

.page-header p {
    color: #888;
    margin-top: 0.5rem;
}

.loading, .empty {
    text-align: center;
    padding: 4rem;
    color: #888;
    font-size: 1.2rem;
    background-color: var(--surface);
    border-radius: 12px;
    border: 1px dashed var(--border);
}

.empty i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--border);
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.notification-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.2rem 1.5rem;
    transition: all 0.2s ease;
}

.notification-card.unread {
    border-left: 4px solid var(--primary);
    background-color: rgba(var(--primary-rgb), 0.05);
}

.notification-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background-color: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.message {
    margin: 0;
    font-size: 1.05rem;
    color: var(--text);
    font-weight: 500;
}

.notification-card.unread .message {
    font-weight: 700;
}

.date {
    font-size: 0.85rem;
    color: #888;
}

.btn-read {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 1.8rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-read:hover {
    background-color: rgba(var(--primary-rgb), 0.1);
    transform: scale(1.1);
}
</style>
