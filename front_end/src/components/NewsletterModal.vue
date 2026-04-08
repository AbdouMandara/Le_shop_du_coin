<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="closeModal(false)">
      <div class="modal-container">
        <button class="close-btn" @click="closeModal(false)">&times;</button>
        
        <div class="modal-layout">
          <!-- Section Image -->
          <div class="modal-cover">
             <div class="cover-overlay">
                <h3>Notre Catalogue</h3>
             </div>
          </div>

          <!-- Section Contenu -->
          <div class="modal-content">
            <h2 class="modal-title">Offre Exclusive !</h2>
            <p class="modal-desc">Saisissez votre numéro WhatsApp pour télécharger instantanément notre nouveau catalogue produit.</p>
            
            <form @submit.prevent="submitForm" class="newsletter-form">
              <div class="form-group">
                <label for="newsletter-email">Adresse Email (Optionnel)</label>
                <input type="email" id="newsletter-email" class="email-input" v-model="emailAddress" placeholder="vous@exemple.com" />
              </div>

              <div class="form-group">
                <label for="newsletter-whatsapp">Numéro WhatsApp</label>
                <div class="phone-input-group">
                  <select v-model="countryCode" class="country-select">
                    <option value="+223">🇲🇱 +223</option>
                    <option value="+221">🇸🇳 +221</option>
                    <option value="+225">🇨🇮 +225</option>
                    <option value="+224">🇬🇳 +224</option>
                    <option value="+226">🇧🇫 +226</option>
                    <option value="+228">🇹🇬 +228</option>
                    <option value="+33">🇫🇷 +33</option>
                    <option value="+212">🇲🇦 +212</option>
                    <option value="+237">🇨🇲 +237</option>
                  </select>
                  <input type="text" id="newsletter-whatsapp" v-model="phoneNumber" placeholder="70 00 00 00" required />
                </div>
              </div>

              <button type="submit" class="submit-btn" :disabled="isLoading">
                <span v-if="isLoading"><i class='bx bx-loader-alt bx-spin'></i> Traitement...</span>
                <span v-else>Télécharger le catalogue</span>
              </button>
              <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';

const isOpen = ref(false);
const isLoading = ref(false);
const errorMessage = ref('');

const emailAddress = ref('');
const countryCode = ref('+223');
const phoneNumber = ref('');

const closeModal = (submitted = false) => {
  isOpen.value = false;
  localStorage.setItem('newsletter_shown', 'true');
};

const triggerDownload = () => {
  const link = document.createElement('a');
  link.href = '/images_mindset_business_shop/WhatsApp Image 2026-04-05 at 14.53.26 (1).jpeg';
  link.download = 'catalogue.jpeg';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const submitForm = async () => {
  if (!phoneNumber.value.trim()) return;
  
  isLoading.value = true;
  errorMessage.value = '';
  
  const fullNumber = `${countryCode.value} ${phoneNumber.value}`;
  
  // Construct payload
  const payload = { whatsapp_number: fullNumber };
  if (emailAddress.value.trim()) payload.email = emailAddress.value.trim();
  
  try {
    // Afin de prévenir l'erreur 419 (CSRF Mismatch) lors d'un POST API en Sanctum SPA :
    await api.get('http://localhost:8000/sanctum/csrf-cookie');
    
    await api.post('/newsletter/subscribe', payload);
    
    // Succès
    triggerDownload();
    closeModal(true);
  } catch (error) {
    if (error.response?.status === 419) {
       errorMessage.value = "Session expirée. Veuillez recharger la page.";
    } else {
       errorMessage.value = "Une erreur s'est produite lors de l'envoi.";
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  const hasSeen = localStorage.getItem('newsletter_shown');
  if (!hasSeen) {
    // 60 000 ms = 1 min
    setTimeout(() => {
      isOpen.value = true;
    }, 60000);
  }
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-container {
  background-color: var(--surface);
  border-radius: 20px;
  width: 90%;
  max-width: 650px;
  position: relative;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  border: 1px solid var(--border);
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: var(--background);
  border: 1px solid var(--border);
  font-size: 1.5rem;
  color: var(--text);
  cursor: pointer;
  width: 35px;
  height: 35px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  z-index: 10;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.close-btn:hover {
  background: #fee2e2; /* light red */
  color: #dc2626;
  border-color: #fca5a5;
}

[data-theme='dark'] .close-btn:hover {
  background: rgba(220, 38, 38, 0.2);
}

.modal-layout {
  display: flex;
  flex-direction: row;
  min-height: 380px;
}

.modal-cover {
  flex: 0.9;
  /* Un splash professionnel et e-commerce */
  background-image: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&q=80&w=600');
  background-size: cover;
  background-position: center;
  position: relative;
}

.cover-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.1));
  display: flex;
  align-items: flex-end;
  padding: 1.5rem;
}

.cover-overlay h3 {
  color: white;
  font-size: 1.4rem;
  margin: 0;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.modal-content {
  flex: 1.1;
  padding: 3rem 2.5rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.modal-title {
  color: var(--text);
  font-size: 1.8rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  font-family: 'Cal Sans', sans-serif;
  letter-spacing: -0.5px;
}

.modal-desc {
  color: #6b7280;
  font-size: 0.95rem;
  margin-bottom: 2rem;
  line-height: 1.5;
}

[data-theme='dark'] .modal-desc {
  color: #9ca3af;
}

.newsletter-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 0.6rem;
  color: var(--text);
}

.phone-input-group {
  display: flex;
  border: 1.5px solid var(--border);
  border-radius: 10px;
  overflow: hidden;
  transition: all 0.3s;
  background-color: var(--background);
}

.phone-input-group:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
}

.country-select {
  background: var(--neutral);
  border: none;
  border-right: 1.5px solid var(--border);
  padding: 0.75rem;
  color: var(--text);
  font-weight: 600;
  cursor: pointer;
  outline: none;
  font-family: inherit;
  font-size: 0.9rem;
}

[data-theme='dark'] .country-select {
  background: var(--surface);
}

.phone-input-group input {
  flex: 1;
  border: none;
  padding: 0.75rem 1rem;
  background: transparent;
  color: var(--text);
  font-size: 1rem;
  outline: none;
  font-family: inherit;
  font-weight: 500;
  width: 100%;
}

.email-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1.5px solid var(--border);
  border-radius: 10px;
  background-color: var(--background);
  color: var(--text);
  font-size: 1rem;
  font-family: inherit;
  transition: all 0.3s;
}

.email-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
}

.submit-btn {
  background-color: var(--primary);
  color: white;
  border: none;
  padding: 1rem 1.5rem;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  width: 100%;
  margin-top: 0.5rem;
}

.submit-btn:hover {
  filter: brightness(0.95);
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(var(--primary-rgb), 0.2);
}

.submit-btn:active {
  transform: translateY(0);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.error-msg {
  color: #ef4444;
  font-size: 0.85rem;
  margin-top: -0.5rem;
  font-weight: 500;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}

@media (max-width: 640px) {
  .modal-layout {
    flex-direction: column;
    min-height: auto;
  }
  .modal-cover {
    display: none;
  }
  .modal-content {
    padding: 2.5rem 1.5rem;
  }
}
</style>
