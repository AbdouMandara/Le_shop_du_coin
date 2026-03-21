<template>
  <div class="register-page">
    <div class="register-card">
      <div class="register-header">
        <i class='bx bxs-user-plus register-logo'></i>
        <h1>Création de compte</h1>
        <p>Rejoignez la communauté E-Shop</p>
      </div>

      <form @submit.prevent="handleRegister" class="register-form">
        <div class="form-group">
          <label for="name">Nom complet</label>
          <div class="input-wrapper">
            <i class='bx bx-user'></i>
            <input 
              id="name" 
              v-model="form.name" 
              type="text" 
              placeholder="Ex: John Doe" 
              maxlength="30"
              required
            />
          </div>
        </div>

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

        <div class="form-group">
          <label for="password_confirmation">Confirmer le mot de passe</label>
          <div class="input-wrapper">
            <i class='bx bx-check-shield'></i>
            <input 
              id="password_confirmation" 
              v-model="form.password_confirmation" 
              type="password" 
              placeholder="••••••••" 
              required
            />
          </div>
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button type="submit" class="btn-register" :disabled="loading">
          <span v-if="!loading">S'inscrire</span>
          <i v-else class='bx bx-loader-alt bx-spin'></i>
        </button>
      </form>

      <div class="register-footer">
        <p>Déjà un compte ? <router-link to="/login">Se connecter</router-link></p>
      </div>
      <router-link to="/" class="back-home">
        <i class='bx bx-arrow-back'></i> Retour à l'accueil
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);
const error = ref(null);

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const handleRegister = async () => {
    loading.value = true;
    error.value = null;
    try {
        await api.get('http://localhost:8000/sanctum/csrf-cookie');
        await api.post('/register', form);
        await authStore.fetchUser();
        router.push({ name: 'home' });
    } catch (err) {
        error.value = err.response?.data?.message || 'Une erreur est survenue';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.register-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: var(--neutral);
  padding: 1.5rem;
}

.register-card {
  width: 100%;
  max-width: 450px;
  background-color: var(--surface);
  border: 1px solid var(--border);
  padding: 2.5rem;
  border-radius: 12px;
}

.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.register-logo {
  font-size: 3rem;
  color: var(--secondary);
  margin-bottom: 1rem;
}

.register-header h1 {
  font-size: 1.75rem;
  margin: 0;
  font-weight: 700;
}

.register-header p {
  color: #666;
  margin: 0.5rem 0 0;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
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
}

.input-wrapper input:focus {
  outline: none;
  border-color: var(--primary);
}

.btn-register {
  background-color: var(--primary);
  color: #FFFFFF;
  border: none;
  padding: 0.85rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1rem;
}

.error-message {
  color: #ff4d4d;
  font-size: 0.85rem;
  text-align: center;
}

.register-footer {
  margin-top: 2rem;
  text-align: center;
  font-size: 0.9rem;
}

.register-footer a {
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
