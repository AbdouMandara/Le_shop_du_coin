import { defineStore } from 'pinia';

export const useFavoritesStore = defineStore('favorites', {
    state: () => ({
        // On charge les IDs depuis le localStorage dès l'initialisation
        items: JSON.parse(localStorage.getItem('favorites') || '[]'),
    }),
    getters: {
        count: (state) => state.items.length,
        isFavorite: (state) => (productId) => state.items.includes(productId),
    },
    actions: {
        toggleFavorite(productId) {
            const index = this.items.indexOf(productId);
            if (index > -1) {
                // Déjà en favori -> on le retire
                this.items.splice(index, 1);
            } else {
                // Pas en favori -> on l'ajoute
                this.items.push(productId);
            }
            this.save();
        },
        save() {
            localStorage.setItem('favorites', JSON.stringify(this.items));
        }
    }
});
