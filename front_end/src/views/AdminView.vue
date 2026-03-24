<template>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Tableau de Bord</h1>
                <p>Bienvenue sur votre espace d'administration.</p>
            </div>
            <div class="header-date">
                <i class='bx bx-calendar'></i>
                <span>{{ currentDate }}</span>
            </div>
        </header>

        <div v-if="loading" class="loading-state">
            <i class='bx bx-loader-alt bx-spin'></i>
            <p>Chargement des données...</p>
        </div>

        <div v-else class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon products">
                    <i class='bx bx-package'></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Produits</span>
                    <h2 class="stat-value">{{ stats.products_count }}</h2>
                </div>
                <router-link to="/admin/products" class="stat-action">
                    <i class='bx bx-chevron-right'></i>
                </router-link>
            </div>

            <div class="stat-card">
                <div class="stat-icon clients">
                    <i class='bx bx-user'></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Clients</span>
                    <h2 class="stat-value">{{ stats.clients_count }}</h2>
                </div>
                <router-link to="/admin/users" class="stat-action">
                    <i class='bx bx-chevron-right'></i>
                </router-link>
            </div>

            <div class="stat-card">
                <div class="stat-icon livreurs">
                    <i class='bx bx-cycling'></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Livreurs</span>
                    <h2 class="stat-value">{{ stats.livreurs_count }}</h2>
                </div>
                <router-link to="/admin/livreurs" class="stat-action">
                    <i class='bx bx-chevron-right'></i>
                </router-link>
            </div>

            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class='bx bx-cart'></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Commandes</span>
                    <h2 class="stat-value">{{ stats.orders_count }}</h2>
                </div>
                <router-link to="/admin/orders" class="stat-action">
                    <i class='bx bx-chevron-right'></i>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/services/api';

const stats = ref({
    products_count: 0,
    clients_count: 0,
    livreurs_count: 0,
    orders_count: 0
});
const loading = ref(true);

const currentDate = computed(() => {
    return new Intl.DateTimeFormat('fr-FR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }).format(new Date());
});

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/stats');
        stats.value = response.data;
    } catch (error) {
        console.error('Erreur lors du chargement des stats:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});
</script>

<style scoped>
.dashboard {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 3rem;
}

.header-content h1 {
    font-size: 2rem;
    font-weight: 800;
    margin: 0;
    color: var(--text);
}

.header-content p {
    color: var(--text-light);
    margin: 0.5rem 0 0;
}

.header-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 0.9rem;
    color: var(--text-light);
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 5rem;
    color: var(--text-light);
}

.loading-state i {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.stat-icon {
    width: 54px;
    height: 54px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.products { background: #eef2ff; color: #4f46e5; }
.stat-icon.clients { background: #ecfdf5; color: #10b981; }
.stat-icon.livreurs { background: #fffbeb; color: #f59e0b; }
.stat-icon.orders { background: #fef2f2; color: #ef4444; }

.stat-info {
    flex: 1;
}

.stat-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0.25rem 0 0;
    color: var(--text);
}

.stat-action {
    color: var(--text-light);
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.stat-card:hover .stat-action {
    color: var(--primary);
    transform: translateX(4px);
}

@media (max-width: 640px) {
    .dashboard-header {
        flex-direction: column;
        gap: 1.5rem;
    }
}
</style>
