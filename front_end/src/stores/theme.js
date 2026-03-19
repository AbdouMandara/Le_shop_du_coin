import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
    state: () => ({
        dark: localStorage.getItem('theme') === 'dark',
    }),
    actions: {
        toggleTheme() {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            this.applyTheme();
        },
        applyTheme() {
            document.documentElement.setAttribute('data-theme', this.dark ? 'dark' : 'light');
        }
    }
});
