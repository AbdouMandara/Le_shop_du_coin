<template>
  <div class="promotion-admin">
    <header class="page-header">
      <div>
        <h1 class="page-title">Gestion des Promotions</h1>
        <p class="page-subtitle">Créez et gérez les offres spéciales de votre boutique</p>
      </div>
      <button @click="openModal()" class="btn btn--primary">
        <i class='bx bx-plus'></i> Nouvelle Promotion
      </button>
    </header>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon stat-icon--active"><i class='bx bx-check-circle'></i></div>
        <div class="stat-content">
          <span class="stat-label">Actives</span>
          <span class="stat-value">{{ activePromotionsCount }}</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--scheduled"><i class='bx bx-time-five'></i></div>
        <div class="stat-content">
          <span class="stat-label">À venir</span>
          <span class="stat-value">{{ upcomingPromotionsCount }}</span>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Type</th>
            <th>Valeur</th>
            <th>Période</th>
            <th>Statut</th>
            <th>Produits</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="promo in promotions" :key="promo.id">
            <td>
              <div class="promo-name">{{ promo.name }}</div>
            </td>
            <td>
              <span class="badge" :class="promo.type === 'percentage' ? 'badge--info' : 'badge--success'">
                {{ promo.type === 'percentage' ? 'Pourcentage' : 'Fixe' }}
              </span>
            </td>
            <td>
              <span class="promo-value">
                {{ promo.type === 'percentage' ? promo.value + '%' : promo.value + '€' }}
              </span>
            </td>
            <td>
              <div class="promo-date">
                <span>Du {{ formatDate(promo.start_date) }}</span>
                <span>Au {{ formatDate(promo.end_date) }}</span>
              </div>
            </td>
            <td>
              <span class="status-pill" :class="'status--' + promo.status">
                {{ promo.status === 'active' ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>
               <button @click="openAssignModal(promo)" class="btn-link">
                 Modifier les produits
               </button>
            </td>
            <td class="text-right">
              <div class="actions-group">
                <button @click="openModal(promo)" class="btn-icon" title="Modifier">
                  <i class='bx bx-edit-alt'></i>
                </button>
                <button @click="deletePromotion(promo.id)" class="btn-icon btn-icon--danger" title="Supprimer">
                  <i class='bx bx-trash'></i>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="promotions.length === 0">
            <td colspan="7" class="text-center empty-state">
              <i class='bx bx-purchase-tag-alt'></i>
              <p>Aucune promotion trouvée. Commencez par en créer une !</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Creation/Edition -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal card">
        <header class="modal-header">
          <h2>{{ editingPromo ? 'Modifier la Promotion' : 'Nouvelle Promotion' }}</h2>
          <button @click="closeModal" class="btn-close"><i class='bx bx-x'></i></button>
        </header>

        <form @submit.prevent="savePromotion" class="promotion-form">
          <div class="form-group">
            <label>Nom de la promotion</label>
            <input v-model="promoForm.name" type="text" placeholder="Ex: Black Friday" required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Type de réduction</label>
              <select v-model="promoForm.type" required>
                <option value="percentage">Pourcentage (%)</option>
                <option value="fixed">Montant fixe (FCFA)</option>
              </select>
            </div>
            <div class="form-group">
              <label>Valeur</label>
              <input v-model.number="promoForm.value" type="number" step="0.01" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Date de début</label>
              <input v-model="promoForm.start_date" type="datetime-local" required />
            </div>
            <div class="form-group">
              <label>Date de fin</label>
              <input v-model="promoForm.end_date" type="datetime-local" required />
            </div>
          </div>

          <div class="form-group">
            <label>Statut</label>
            <select v-model="promoForm.status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <footer class="modal-footer">
            <button type="button" @click="closeModal" class="btn btn--outline">Annuler</button>
            <button type="submit" class="btn btn--primary" :disabled="loading">
              {{ loading ? 'Enregistrement...' : (editingPromo ? 'Mettre à jour' : 'Créer') }}
            </button>
          </footer>
        </form>
      </div>
    </div>

    <!-- Modal Assignment -->
    <div v-if="showAssignModal" class="modal-overlay" @click.self="closeAssignModal">
      <div class="modal modal--large card">
        <header class="modal-header">
          <h2>Assigner à des produits : {{ currentPromo?.name }}</h2>
          <button @click="closeAssignModal" class="btn-close"><i class='bx bx-x'></i></button>
        </header>

        <div class="assignment-content">
          <div class="search-box">
             <i class='bx bx-search'></i>
             <input v-model="productSearch" type="text" placeholder="Rechercher un produit..." />
          </div>

          <div class="products-list">
             <div v-for="product in filteredProducts" :key="product.id" class="product-item">
                <img :src="product.image" :alt="product.name" class="product-mini-img" />
                <div class="product-info">
                   <span class="product-name">{{ product.name }}</span>
                   <span class="product-price">{{ product.price }}€</span>
                </div>
                <button 
                  @click="toggleAssignment(product)" 
                  class="btn-assign"
                  :class="{ 'btn-assign--active': isAssigned(product) }"
                >
                  <i :class="isAssigned(product) ? 'bx bx-check-circle' : 'bx bx-plus-circle'"></i>
                  {{ isAssigned(product) ? 'Assigné' : 'Assigner' }}
                </button>
             </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/services/api';

const promotions = ref([]);
const products = ref([]);
const loading = ref(false);
const showModal = ref(false);
const showAssignModal = ref(false);
const editingPromo = ref(null);
const currentPromo = ref(null);
const productSearch = ref('');

const promoForm = ref({
  name: '',
  type: 'percentage',
  value: 0,
  start_date: '',
  end_date: '',
  status: 'active'
});

const fetchData = async () => {
  try {
    const [promoRes, prodRes] = await Promise.all([
      api.get('/admin/promotions'),
      api.get('/admin/products')
    ]);
    promotions.value = promoRes.data;
    // API returns products as an array directly or inside data
    products.value = prodRes.data.data || prodRes.data;
  } catch (error) {
    console.error('Erreur lors du chargement des données', error);
  }
};

onMounted(fetchData);

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
};

const activePromotionsCount = computed(() => {
  return promotions.value.filter(p => p.status === 'active' && new Date(p.end_date) > new Date()).length;
});

const upcomingPromotionsCount = computed(() => {
  return promotions.value.filter(p => new Date(p.start_date) > new Date()).length;
});

const openModal = (promo = null) => {
  if (promo) {
    editingPromo.value = promo;
    promoForm.value = { ...promo };
    // Format dates for datetime-local input (YYYY-MM-DDThh:mm)
    promoForm.value.start_date = new Date(promo.start_date).toISOString().slice(0, 16);
    promoForm.value.end_date = new Date(promo.end_date).toISOString().slice(0, 16);
  } else {
    editingPromo.value = null;
    promoForm.value = {
      name: '',
      type: 'percentage',
      value: 0,
      start_date: '',
      end_date: '',
      status: 'active'
    };
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const savePromotion = async () => {
  loading.value = true;
  try {
    if (editingPromo.value) {
      await api.put(`/admin/promotions/${editingPromo.value.id}`, promoForm.value);
    } else {
      await api.post('/admin/promotions', promoForm.value);
    }
    await fetchData();
    closeModal();
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement', error);
    alert(error.response?.data?.message || 'Une erreur est survenue');
  } finally {
    loading.value = false;
  }
};

const deletePromotion = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette promotion ?')) return;
  try {
    await api.delete(`/admin/promotions/${id}`);
    await fetchData();
  } catch (error) {
    console.error('Erreur lors de la suppression', error);
  }
};

// Assignment Logic
const openAssignModal = (promo) => {
  currentPromo.value = promo;
  showAssignModal.value = true;
};

const closeAssignModal = () => {
  showAssignModal.value = false;
};

const filteredProducts = computed(() => {
  if (!productSearch.value) return products.value;
  return products.value.filter(p => 
    p.name.toLowerCase().includes(productSearch.value.toLowerCase())
  );
});

const isAssigned = (product) => {
  if (!currentPromo.value) return false;
  // Checking active_promotion in ProductResource output
  return product.active_promotion?.id === currentPromo.value.id;
};

const toggleAssignment = async (product) => {
  try {
    if (isAssigned(product)) {
      await api.delete(`/admin/products/${product.id}/promotions/${currentPromo.value.id}`);
    } else {
      await api.post(`/admin/products/${product.id}/promotions/${currentPromo.value.id}`);
    }
    // Update local product data
    await fetchData();
  } catch (error) {
    console.error('Erreur lors de l\'assignation', error);
  }
};
</script>

<style scoped>
.promotion-admin {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.page-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 4px;
}

.page-subtitle {
  color: #666;
  font-size: 0.95rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-icon--active { background: #E3FCEF; color: #00875A; }
.stat-icon--scheduled { background: #E6FCFF; color: #00B8D9; }

.stat-label { display: block; font-size: 0.85rem; color: #666; }
.stat-value { font-size: 1.2rem; font-weight: 700; color: var(--text); }

.table-container {
  overflow-x: auto;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.data-table th {
  text-align: left;
  padding: 16px 20px;
  background: #f9fafb;
  font-weight: 600;
  color: #4b5563;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table td {
  padding: 16px 20px;
  border-top: 1px solid #f3f4f6;
  vertical-align: middle;
}

.promo-name { font-weight: 600; color: var(--text); }
.promo-value { font-weight: 700; color: var(--primary); font-size: 1.1rem; }

.promo-date {
  display: flex;
  flex-direction: column;
  font-size: 0.85rem;
  color: #666;
}

.badge {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge--info { background: #E6FCFF; color: #00B8D9; }
.badge--success { background: #E3FCEF; color: #00875A; }

.status-pill {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status--active { background: #E3FCEF; color: #00875A; }
.status--inactive { background: #F4F5F7; color: #4b5563; }

.actions-group {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  color: #666;
  transition: all 0.2s;
}

.btn-icon:hover { background: #f9fafb; color: var(--primary); }
.btn-icon--danger:hover { color: #ef4444; border-color: #fee2e2; }

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
}

.modal--large { max-width: 800px; }

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 { font-size: 1.25rem; font-weight: 700; }

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #9ca3af;
}

.promotion-form { padding: 24px; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; }
.form-group input, .form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.modal-footer {
  margin-top: 32px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* Assignment Styles */
.assignment-content { padding: 20px; }
.search-box {
  position: relative;
  margin-bottom: 20px;
}
.search-box i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}
.search-box input {
  width: 100%;
  padding: 12px 14px 12px 40px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
}

.products-list {
  max-height: 400px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  border: 1px solid #f3f4f6;
  border-radius: 10px;
}

.product-mini-img {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
}

.product-info { flex: 1; display: flex; flex-direction: column; }
.product-name { font-weight: 600; font-size: 0.95rem; }
.product-price { font-size: 0.85rem; color: #666; }

.btn-assign {
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid var(--primary);
  background: white;
  color: var(--primary);
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-assign--active { background: var(--primary); color: white; }

.empty-state {
  padding: 48px 0;
  color: #9ca3af;
}
.empty-state i { font-size: 3rem; margin-bottom: 16px; }

.text-right { text-align: right; }
.text-center { text-align: center; }

/* Shared Buttons from your design system if any, otherwise standard ones */
.btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  border: none;
  transition: opacity 0.2s;
}
.btn--primary { background: var(--primary); color: white; }
.btn--outline { background: white; border: 1px solid #d1d5db; color: #374151; }
.btn:hover { opacity: 0.9; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
