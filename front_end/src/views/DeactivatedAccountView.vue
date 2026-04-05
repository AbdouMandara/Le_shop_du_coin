<template>
  <div class="deactivated-container">
    <div class="deactivated-card">
      <div class="icon-wrapper">
        <i class='bx bx-block'></i>
      </div>
      <h1>Compte Désactivé</h1>
      <p>Votre compte a été suspendu ou désactivé par l'administration.</p>
      <p class="contact-info">
        Veuillez contacter l'administrateur de la plateforme pour plus d'informations ou pour demander la réactivation de votre compte.
      </p>
      <div class="actions">
        <button @click="handleLogout" class="btn-primary">
          <i class='bx bx-log-out'></i> Déconnexion
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>

<style scoped>
.deactivated-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--background, #f3f4f6);
    padding: 2rem;
}

.deactivated-card {
    background: var(--surface, #ffffff);
    padding: 3rem 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    text-align: center;
    max-width: 450px;
    width: 100%;
    animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes popIn {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    background: #fbe6e6;
    color: #d32f2f;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto 1.5rem;
}

h1 {
    font-size: 1.8rem;
    color: var(--text, #111827);
    margin-bottom: 1rem;
    font-weight: 800;
}

p {
    color: #4b5563;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.contact-info {
    font-size: 0.95rem;
    color: #6b7280;
    margin-bottom: 2.5rem;
    line-height: 1.5;
}

.actions {
    display: flex;
    justify-content: center;
}

.btn-primary {
    background: var(--primary, #3b82f6);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}
</style>
