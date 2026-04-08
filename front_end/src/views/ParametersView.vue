<template>
  <div class="params-page">

    <!-- Page Header -->
    <div class="params-header">
      <h1 class="params-title">
        <i class='bx bx-cog'></i> Paramètres du compte
      </h1>
      <p class="params-subtitle">Gérez vos informations personnelles et votre sécurité</p>
    </div>

    <!-- Success / Error banners -->
    <transition name="fade">
      <div v-if="successMsg" class="alert alert-success">
        <i class='bx bx-check-circle'></i> {{ successMsg }}
      </div>
    </transition>
    <transition name="fade">
      <div v-if="errorMsg" class="alert alert-error">
        <i class='bx bx-error-circle'></i> {{ errorMsg }}
      </div>
    </transition>

    <div class="params-grid">

      <!-- ============================
           CARD 1 — Avatar
      ============================= -->
      <div class="param-card avatar-card">
        <div class="card-label">
          <i class='bx bx-camera'></i> Photo de profil
        </div>

        <div class="avatar-zone">
          <!-- Preview ring -->
          <div class="avatar-ring" @click="triggerFileInput" :title="'Changer la photo'">
            <img
              v-if="avatarPreview"
              :src="avatarPreview"
              alt="Avatar"
              class="avatar-img"
            />
            <div v-else class="avatar-placeholder">
              <i class='bx bx-user'></i>
            </div>
            <div class="avatar-overlay">
              <i class='bx bx-camera'></i>
            </div>
          </div>

          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden-input"
            @change="onFileChange"
          />

          <div class="avatar-info">
            <p class="avatar-name">{{ form.name }}</p>
            <p class="avatar-role">{{ roleLabel }}</p>
            <button
              v-if="avatarPreview && avatarFile"
              type="button"
              class="btn-remove-photo"
              @click="removePhoto"
            >
              <i class='bx bx-trash'></i> Supprimer
            </button>
          </div>
        </div>

        <p class="avatar-hint">
          <i class='bx bx-info-circle'></i>
          JPG, PNG, WebP — max. 2 Mo. La photo est optionnelle.
        </p>
      </div>

      <!-- ============================
           CARD 2 — Identité
      ============================= -->
      <div class="param-card">
        <div class="card-label">
          <i class='bx bx-user-circle'></i> Informations personnelles
        </div>

        <form @submit.prevent="handleUpdate" class="param-form" id="profile-form">

          <div class="form-group">
            <label for="param-name">Nom complet</label>
            <div class="input-wrapper">
              <i class='bx bx-user'></i>
              <input
                id="param-name"
                v-model="form.name"
                type="text"
                placeholder="Votre nom"
                maxlength="30"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="param-email">Adresse email</label>
            <div class="input-wrapper">
              <i class='bx bx-envelope'></i>
              <input
                id="param-email"
                v-model="form.email"
                type="email"
                placeholder="votre@email.com"
                required
              />
            </div>
          </div>

          <div class="section-divider">
            <span>Changer le mot de passe (Optionnel)</span>
          </div>

          <div class="form-group">
            <label for="param-current-pwd">Mot de passe actuel</label>
            <div class="input-wrapper">
              <i class='bx bx-lock'></i>
              <input
                id="param-current-pwd"
                v-model="form.current_password"
                :type="showCurrentPwd ? 'text' : 'password'"
                placeholder="••••••••"
              />
              <button type="button" class="btn-eye" @click="showCurrentPwd = !showCurrentPwd">
                <i :class="showCurrentPwd ? 'bx bx-hide' : 'bx bx-show'"></i>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="param-new-pwd">Nouveau mot de passe</label>
            <div class="input-wrapper">
              <i class='bx bx-lock-alt'></i>
              <input
                id="param-new-pwd"
                v-model="form.password"
                :type="showNewPwd ? 'text' : 'password'"
                placeholder="Minimum 8 caractères"
              />
              <button type="button" class="btn-eye" @click="showNewPwd = !showNewPwd">
                <i :class="showNewPwd ? 'bx bx-hide' : 'bx bx-show'"></i>
              </button>
            </div>
            <!-- strength bar -->
            <div v-if="form.password" class="strength-bar">
              <div class="strength-fill" :class="strengthClass" :style="{ width: strengthWidth }"></div>
            </div>
            <span v-if="form.password" class="strength-label" :class="strengthClass">
              {{ strengthLabel }}
            </span>
          </div>

          <div class="form-group">
            <label for="param-confirm-pwd">Confirmer le nouveau mot de passe</label>
            <div class="input-wrapper">
              <i class='bx bx-check-shield'></i>
              <input
                id="param-confirm-pwd"
                v-model="form.password_confirmation"
                :type="showConfirmPwd ? 'text' : 'password'"
                placeholder="••••••••"
              />
              <button type="button" class="btn-eye" @click="showConfirmPwd = !showConfirmPwd">
                <i :class="showConfirmPwd ? 'bx bx-hide' : 'bx bx-show'"></i>
              </button>
            </div>
            <p v-if="pwdMismatch" class="field-error">
              <i class='bx bx-x-circle'></i> Les mots de passe ne correspondent pas.
            </p>
          </div>

          <button
            type="submit"
            class="btn-save"
            :disabled="loading"
            form="profile-form"
          >
            <span v-if="!loading">
              <i class='bx bx-save'></i> Enregistrer les modifications
            </span>
            <span v-else>
              <i class='bx bx-loader-alt bx-spin'></i> Enregistrement…
            </span>
          </button>

        </form>
      </div>

    </div><!-- /params-grid -->
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const authStore = useAuthStore();
const router = useRouter();

// ── State ──────────────────────────────────────────────
const loading      = ref(false);
const successMsg   = ref('');
const errorMsg     = ref('');
const fileInput    = ref(null);
const avatarFile   = ref(null);
const avatarPreview = ref(null);

const showCurrentPwd = ref(false);
const showNewPwd     = ref(false);
const showConfirmPwd = ref(false);

const form = reactive({
  name:                  '',
  email:                 '',
  current_password:      '',
  password:              '',
  password_confirmation: '',
});

// ── Derived ────────────────────────────────────────────
const roleLabel = computed(() => {
  const role = authStore.user?.role;
  const map  = { admin: 'Administrateur', user: 'Client', livreur: 'Livreur' };
  return map[typeof role === 'string' ? role : role?.label] || 'Utilisateur';
});



// Password strength
const strength = computed(() => {
  const p = form.password;
  if (!p) return 0;
  let score = 0;
  if (p.length >= 8)  score++;
  if (p.length >= 12) score++;
  if (/[A-Z]/.test(p)) score++;
  if (/[0-9]/.test(p)) score++;
  if (/[^A-Za-z0-9]/.test(p)) score++;
  return score;
});

const strengthClass = computed(() => {
  if (strength.value <= 1) return 'weak';
  if (strength.value <= 3) return 'medium';
  return 'strong';
});

const strengthWidth = computed(() => {
  return `${Math.min(100, strength.value * 20)}%`;
});

const strengthLabel = computed(() => {
  if (strength.value <= 1) return 'Faible';
  if (strength.value <= 3) return 'Moyen';
  return 'Fort';
});

// ── Init ───────────────────────────────────────────────
onMounted(async () => {
  if (!authStore.user) await authStore.fetchUser();
  const u = authStore.user;
  if (!u) {
    // Session expired — redirect to login
    router.push({ name: 'login' });
    return;
  }
  form.name  = u.name  || '';
  form.email = u.email || '';
  avatarPreview.value = u.profile_photo || null;
});

// ── Avatar handling ────────────────────────────────────
const triggerFileInput = () => fileInput.value?.click();

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  avatarFile.value = file;
  avatarPreview.value = URL.createObjectURL(file);
};

const removePhoto = () => {
  avatarFile.value    = null;
  avatarPreview.value = authStore.user?.profile_photo || null;
  if (fileInput.value) fileInput.value.value = '';
};

// ── Submit ─────────────────────────────────────────────
const handleUpdate = async () => {
  if (pwdMismatch.value) return;
  loading.value    = true;
  successMsg.value = '';
  errorMsg.value   = '';

  try {
    const data = new FormData();
    data.append('name',  form.name);
    data.append('email', form.email);

    if (avatarFile.value) {
      data.append('profile_photo', avatarFile.value);
    }

    if (form.password) {
      data.append('current_password',      form.current_password);
      data.append('password',              form.password);
      data.append('password_confirmation', form.password_confirmation);
    }

    const res = await api.post('/profile', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    // Sync store
    await authStore.fetchUser();

    // Reset avatar tracking
    avatarFile.value    = null;
    avatarPreview.value = authStore.user?.profile_photo || null;

    // Reset password fields
    form.current_password = '';
    form.password = '';
    form.password_confirmation = '';

    successMsg.value = res.data.message || 'Profil mis à jour avec succès !';
    setTimeout(() => (successMsg.value = ''), 4000);

  } catch (err) {
    const errors = err.response?.data?.errors;
    if (errors) {
      const first = Object.values(errors)[0];
      errorMsg.value = Array.isArray(first) ? first[0] : first;
    } else {
      errorMsg.value = err.response?.data?.message || 'Une erreur est survenue.';
    }
    setTimeout(() => (errorMsg.value = ''), 5000);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.params-page {
  max-width: 960px;
  margin: 0 auto;
  padding: 2rem 1.5rem 4rem;
}

.params-header {
  margin-bottom: 2rem;
}

.params-title {
  font-size: 1.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin: 0 0 0.35rem;
  color: var(--text);
}

.params-title i {
  color: var(--primary);
}

.params-subtitle {
  color: #888;
  margin: 0;
  font-size: 0.95rem;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.85rem 1.25rem;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 1.5rem;
}

.alert-success {
  background: rgba(34, 197, 94, 0.12);
  border: 1px solid rgba(34, 197, 94, 0.35);
  color: #16a34a;
}

.alert-error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #dc2626;
}

.params-grid {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 700px) {
  .params-grid {
    grid-template-columns: 1fr;
  }
}

.param-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 1.75rem;
}

.card-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--primary);
  margin-bottom: 1.5rem;
}

/* ── Avatar Card ────────────────────────────────────── */
.avatar-card {
  text-align: center;
}

.avatar-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.avatar-ring {
  position: relative;
  width: 110px;
  height: 110px;
  border-radius: 50%;
  cursor: pointer;
  border: 3px solid var(--primary);
  box-shadow: 0 0 0 4px rgba(var(--primary-rgb, 99, 102, 241), 0.15);
  transition: box-shadow 0.25s;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar-ring:hover {
  box-shadow: 0 0 0 6px rgba(var(--primary-rgb, 99, 102, 241), 0.25);
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--neutral, #f3f4f6);
  font-size: 3rem;
  color: #bbb;
}

.avatar-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #fff;
  opacity: 0;
  transition: opacity 0.2s;
  border-radius: 50%;
}

.avatar-ring:hover .avatar-overlay {
  opacity: 1;
}

.hidden-input {
  display: none;
}

.avatar-name {
  font-weight: 700;
  font-size: 1rem;
  margin: 0;
  color: var(--text);
}

.avatar-role {
  font-size: 0.82rem;
  color: #888;
  margin: 0.15rem 0 0;
}

.btn-remove-photo {
  margin-top: 0.5rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #dc2626;
  padding: 0.35rem 0.85rem;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  transition: background 0.2s;
}

.btn-remove-photo:hover {
  background: rgba(239, 68, 68, 0.2);
}

.avatar-hint {
  font-size: 0.78rem;
  color: #aaa;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  margin: 0;
  line-height: 1.5;
}

/* ── Form ───────────────────────────────────────────── */
.param-form {
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
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper > i:first-child {
  position: absolute;
  left: 1rem;
  font-size: 1.2rem;
  color: #aaa;
  pointer-events: none;
}

.input-wrapper input {
  width: 100%;
  padding: 0.75rem 3rem 0.75rem 2.75rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: var(--background);
  color: var(--text);
  font-family: inherit;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-wrapper input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 99, 102, 241), 0.12);
}

.btn-eye {
  position: absolute;
  right: 0.9rem;
  background: none;
  border: none;
  color: #999;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  transition: color 0.2s;
}

.btn-eye:hover { color: var(--primary); }

/* ── Section divider ────────────────────────────────── */
.section-divider {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 0.25rem 0;
}

.section-divider::before,
.section-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

.section-divider span {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #aaa;
  white-space: nowrap;
}

/* ── Strength bar ───────────────────────────────────── */
.strength-bar {
  height: 4px;
  background: var(--border);
  border-radius: 99px;
  overflow: hidden;
  margin-top: 0.35rem;
}

.strength-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.4s, background 0.4s;
}

.strength-fill.weak   { background: #ef4444; }
.strength-fill.medium { background: #f59e0b; }
.strength-fill.strong { background: #22c55e; }

.strength-label {
  font-size: 0.75rem;
  font-weight: 600;
}

.strength-label.weak   { color: #ef4444; }
.strength-label.medium { color: #f59e0b; }
.strength-label.strong { color: #22c55e; }

/* ── Field error ────────────────────────────────────── */
.field-error {
  font-size: 0.8rem;
  color: #ef4444;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  margin: 0;
}

/* ── Save button ────────────────────────────────────── */
.btn-save {
  margin-top: 0.5rem;
  background: var(--primary);
  color: #fff;
  border: none;
  padding: 0.85rem;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: opacity 0.2s, transform 0.15s;
}

.btn-save:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}

.btn-save:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

/* ── Fade transition ────────────────────────────────── */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.35s, transform 0.35s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>