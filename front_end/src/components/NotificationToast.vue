<template>
  <div class="notification-container">
    <transition-group name="toast">
      <div 
        v-for="notification in notificationStore.notifications" 
        :key="notification.id" 
        :class="['toast', notification.type]"
      >
        <div class="toast-icon">
          <i v-if="notification.type === 'success'" class='bx bxs-check-circle'></i>
          <i v-else-if="notification.type === 'error'" class='bx bxs-error-circle'></i>
          <i v-else-if="notification.type === 'warning'" class='bx bxs-message-square-error'></i>
          <i v-else class='bx bxs-info-circle'></i>
        </div>
        <div class="toast-content">
          {{ notification.message }}
        </div>
        <button class="toast-close" @click="notificationStore.removeNotification(notification.id)">
          &times;
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useNotificationStore } from '@/stores/notifications';

const notificationStore = useNotificationStore();
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}

.toast {
  pointer-events: auto;
  min-width: 300px;
  max-width: 450px;
  background-color: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  position: relative;
  overflow: hidden;
}

.toast::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
}

/* Types */
.toast.success::before { background-color: #4CAF50; }
.toast.success .toast-icon { color: #4CAF50; }

.toast.error::before { background-color: #f44336; }
.toast.error .toast-icon { color: #f44336; }

.toast.warning::before { background-color: #ff9800; }
.toast.warning .toast-icon { color: #ff9800; }

.toast.info::before { background-color: #2196F3; }
.toast.info .toast-icon { color: #2196F3; }

.toast-icon {
  font-size: 1.5rem;
  display: flex;
  align-items: center;
}

.toast-content {
  flex: 1;
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--text);
}

.toast-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #999;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.toast-close:hover {
  color: var(--text);
}

/* Animations */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100px) scale(0.9);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(50px);
}
</style>