<template>
  <div class="livreurs-management">
    <header class="page-header">
      <div class="header-content">
        <h1>Gestion des Livreurs</h1>
        <p>Gérez les comptes des livreurs de la plateforme.</p>
      </div>
      <button @click="showAddModal = true" class="btn-primary">
        <i class='bx bx-plus'></i> Ajouter un Livreur
      </button>
    </header>

    <div v-if="loading" class="loading">
      <i class='bx bx-loader-alt bx-spin'></i> Chargement des livreurs...
    </div>

    <div v-else-if="livreurs.length === 0" class="empty-state">
      <i class='bx bx-user-x'></i>
      <p>Aucun livreur trouvé.</p>
    </div>

    <div v-else class="table-container">
      <table class="livreurs-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Date d'inscription</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="livreur in livreurs" :key="livreur.id">
            <td>
              <div class="user-cell">
                <div class="avatar">{{ livreur.name.charAt(0) }}</div>
                <span>{{ livreur.name }}</span>
              </div>
            </td>
            <td>{{ livreur.email }}</td>
            <td>{{ formatDate(livreur.created_at) }}</td>
            <td>
              <button @click="confirmDelete(livreur)" class="btn-icon btn-delete" title="Supprimer">
                <i class='bx bx-trash'></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Ajouter un Livreur -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Ajouter un nouveau Livreur</h2>
          <button @click="showAddModal = false" class="btn-close">&times;</button>
        </div>
        <form @submit.prevent="handleAddLivreur" class="modal-body">
          <div class="form-group">
            <label>Nom complet</label>
            <input v-model="newLivreur.name" type="text" placeholder="ex: Jean Dupont" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="newLivreur.email" type="email" placeholder="ex: jean@example.com" required />
          </div>
          <div class="form-group">
            <label>Mot de passe</label>
            <input v-model="newLivreur.password" type="password" placeholder="Min. 8 caractères" required />
          </div>
          <div class="modal-footer">
            <button type="button" @click="showAddModal = false" class="btn-secondary">Annuler</button>
            <button type="submit" class="btn-primary" :disabled="submitting">
              {{ submitting ? 'Création...' : 'Créer le compte' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';

const livreurs = ref([]);
const roles = ref([]);
const loading = ref(true);
const submitting = ref(false);
const showAddModal = ref(false);

const newLivreur = ref({
    name: '',
    email: '',
    password: ''
});

const fetchLivreurs = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/users?role=livreur');
        livreurs.value = response.data.data;
    } catch (err) {
        console.error('Erreur lors du chargement des livreurs:', err);
    } finally {
        loading.value = false;
    }
};

const fetchRoles = async () => {
    try {
        const response = await api.get('/admin/roles');
        roles.value = response.data;
    } catch (err) {
        console.error('Erreur lors du chargement des rôles:', err);
    }
};

const handleAddLivreur = async () => {
    submitting.value = true;
    try {
        const livreurRole = roles.value.find(r => r.label === 'livreur');
        if (!livreurRole) throw new Error('Rôle livreur non trouvé');

        await api.post('/admin/users', {
            ...newLivreur.value,
            role_id: livreurRole.id
        });

        await fetchLivreurs();
        showAddModal.value = false;
        newLivreur.value = { name: '', email: '', password: '' };
    } catch (err) {
        alert(err.response?.data?.message || 'Erreur lors de la création');
    } finally {
        submitting.value = false;
    }
};

const confirmDelete = async (livreur) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le compte de ${livreur.name} ?`)) {
        try {
            await api.delete(`/admin/users/${livreur.id}`);
            await fetchLivreurs();
        } catch (err) {
            alert('Erreur lors de la suppression');
        }
    }
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

onMounted(async () => {
    await Promise.all([fetchLivreurs(), fetchRoles()]);
});
</script>

<style scoped>
.livreurs-management {
    padding: 2rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.header-content h1 {
    margin: 0;
    font-size: 1.8rem;
    color: var(--text);
}

.header-content p {
    color: #666;
    margin: 0.5rem 0 0;
}

.loading, .empty-state {
    text-align: center;
    padding: 4rem;
    background: var(--surface);
    border-radius: 12px;
    border: 1px solid var(--border);
    color: #666;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.table-container {
    background: var(--surface);
    border-radius: 12px;
    border: 1px solid var(--border);
    overflow: hidden;
}

.livreurs-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.livreurs-table th {
    padding: 1rem 1.5rem;
    background: var(--neutral);
    color: #666;
    font-weight: 600;
    font-size: 0.9rem;
    border-bottom: 1px solid var(--border);
}

.livreurs-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    color: var(--text);
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.avatar {
    width: 32px;
    height: 32px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
}

.btn-icon {
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s;
}

.btn-delete { color: #ff4d4d; }
.btn-delete:hover { background: #fff5f5; }

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: var(--surface);
    width: 100%;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 { margin: 0; font-size: 1.3rem; }

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text);
}

.form-group input {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--background);
    color: var(--text);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1rem;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.btn-secondary {
    background: var(--neutral);
    color: var(--text);
    border: 1px solid var(--border);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}
</style>
