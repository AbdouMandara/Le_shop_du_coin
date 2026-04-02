<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <h1>Connexion</h1>
        <p>Accédez à votre espace E-Shop</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-wrapper">
            <i class='bx bx-envelope'></i>
            <input 
              id="email" 
              v-model="form.email" 
              type="email" 
              placeholder="votre@email.com" 
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <div class="input-wrapper">
            <i class='bx bx-lock-alt'></i>
            <input 
              id="password" 
              v-model="form.password" 
              type="password" 
              placeholder="••••••••" 
              required
            />
          </div>
        </div>

        <div v-if="authStore.error" class="error-message">
          {{ authStore.error }}
        </div>

        <button type="submit" class="btn-login" :disabled="authStore.loading">
          <span v-if="!authStore.loading">Se connecter</span>
          <i v-else class='bx bx-loader-alt bx-spin'></i>
        </button>
      </form>

      <div class="login-footer">
        <p>Pas encore de compte ? <router-link to="/register">S'inscrire</router-link></p>
        <router-link to="/" class="back-home">
            <i class='bx bx-arrow-back'></i> Retour à l'accueil
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const form = reactive({
    email: '',
    password: '',
});

const handleLogin = async () => {
    try {
        await authStore.login(form);
        
        // Redirect based on role
        if (authStore.isAdmin) {
            router.push({ name: 'admin' });
        } else if (authStore.isLivreur) {
            router.push({ name: 'livreur' });
        } else {
            router.push({ name: 'client-home' });
        }
    } catch (err) {
        // Error handled in store
    }
};
</script>

<style scoped>
.login-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: var(--neutral);
  padding: 1.5rem;
}

.login-card {
  width: 100%;
  max-width: 400px;
  background-color: var(--surface);
  border: 1px solid var(--border);
  padding: 2.5rem;
  border-radius: 12px;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-logo {
  font-size: 3rem;
  color: var(--primary);
  margin-bottom: 1rem;
}

.login-header h1 {
  font-size: 1.75rem;
  margin: 0;
  font-weight: 700;
}

.login-header p {
  color: #666;
  margin: 0.5rem 0 0;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 600;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper i {
  position: absolute;
  left: 1rem;
  font-size: 1.25rem;
  color: #999;
}

.input-wrapper input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  border: 1px solid var(--border);
  border-radius: 8px;
  background-color: var(--background);
  color: var(--text);
  font-family: inherit;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.input-wrapper input:focus {
  outline: none;
  border-color: var(--primary);
}

.btn-login {
  background-color: var(--primary);
  color: #FFFFFF;
  border: none;
  padding: 0.85rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-login:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  color: #ff4d4d;
  font-size: 0.85rem;
  text-align: center;
}

.login-footer {
  margin-top: 2rem;
  text-align: center;
  font-size: 0.9rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.login-footer a {
  color: var(--secondary);
  font-weight: 600;
}

.back-home {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #666 !important;
    font-weight: 400 !important;
}
</style>
