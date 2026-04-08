<template>
  <div class="admin-newsletter-container">
    <div class="header-section">
      <h1 class="page-title">Prospects Newsletter</h1>
      <p class="subtitle">Liste des potentiels clients ayant téléchargé le catalogue</p>
    </div>

    <div v-if="isLoading" class="loading-state">
      <i class='bx bx-loader-alt bx-spin'></i> Chargement des prospects...
    </div>

    <div v-else-if="newsletters.length === 0" class="empty-state">
      <div class="empty-icon"><i class='bx bx-ghost'></i></div>
      <h3>Aucun prospect trouvé</h3>
      <p>Personne n'a encore téléchargé le catalogue via la popup.</p>
    </div>

    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Numéro WhatsApp</th>
            <th>Date d'inscription</th>
            <th class="actions-col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="prospect in newsletters" :key="prospect.id">
            <td>#{{ prospect.id }}</td>
            <td>
              <span v-if="prospect.email" class="email-badge">{{ prospect.email }}</span>
              <span v-else class="text-muted">Non renseigné</span>
            </td>
            <td>
              <span class="whatsapp-badge">{{ prospect.whatsapp_number }}</span>
            </td>
            <td>{{ formatDate(prospect.created_at) }}</td>
            <td class="actions-cell">
              <a 
                v-if="prospect.email" 
                :href="'mailto:' + prospect.email" 
                class="action-btn email-btn"
                title="Envoyer un email"
              >
                <i class='bx bx-envelope'></i> Email
              </a>
              <a 
                :href="'https://wa.me/' + cleanNumber(prospect.whatsapp_number)" 
                target="_blank" 
                class="action-btn whatsapp-btn"
                title="Contacter sur WhatsApp"
              >
                <i class='bx bxl-whatsapp'></i> WhatsApp
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';

const newsletters = ref([]);
const isLoading = ref(true);

const fetchNewsletters = async () => {
  try {
    const response = await api.get('/admin/newsletters');
    newsletters.value = response.data;
  } catch (error) {
    console.error("Erreur lors de la récupération des prospects:", error);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  }).format(date);
};

const cleanNumber = (num) => {
  if(!num) return '';
  return num.replace(/\D/g, '');
};

onMounted(() => {
  fetchNewsletters();
});
</script>

<style scoped>
.admin-newsletter-container {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.header-section {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  color: var(--text);
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #666;
  font-size: 1rem;
}

[data-theme='dark'] .subtitle {
  color: #aaa;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 4rem;
  background-color: var(--surface);
  border-radius: 12px;
  border: 1px dashed var(--border);
}

.loading-state i {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 1rem;
}

.empty-icon {
  font-size: 3rem;
  color: var(--border);
  margin-bottom: 1rem;
}

.table-container {
  background: var(--surface);
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
  border: 1px solid var(--border);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 1rem 1.5rem;
  text-align: left;
  border-bottom: 1px solid var(--border);
}

.data-table th {
  background-color: rgba(0,0,0,0.02);
  font-weight: 600;
  color: var(--text);
  white-space: nowrap;
}

[data-theme='dark'] .data-table th {
  background-color: rgba(255,255,255,0.02);
}

.data-table tbody tr:hover {
  background-color: rgba(var(--primary-rgb), 0.02);
}

.email-badge, .whatsapp-badge {
  display: inline-block;
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
}

.email-badge {
  background-color: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.whatsapp-badge {
  background-color: rgba(34, 197, 94, 0.1);
  color: #22c55e;
}

.text-muted {
  color: #999;
  font-style: italic;
  font-size: 0.85rem;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.2s;
}

.email-btn {
  background-color: #3b82f6;
  color: white;
}

.email-btn:hover {
  background-color: #2563eb;
}

.whatsapp-btn {
  background-color: #25D366;
  color: white;
}

.whatsapp-btn:hover {
  background-color: #128C7E;
}
</style>
