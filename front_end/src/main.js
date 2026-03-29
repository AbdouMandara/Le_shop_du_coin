import './assets/main.css'
import 'boxicons/css/boxicons.min.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Vue3Lottie from 'vue3-lottie'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(Vue3Lottie)
app.use(router)

app.mount('#app')
