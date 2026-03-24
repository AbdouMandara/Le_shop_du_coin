import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { guest: true }
    },
    {
      path: '/',
      component: () => import('../layouts/MainLayout.vue'),
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('../views/HomeView.vue')
        },
        {
          path: 'products',
          name: 'products',
          component: () => import('../views/ProductsView.vue')
        },
        // Client specific routes
        { path: 'client', name: 'client-home', component: () => import('../views/HomeView.vue'), meta: { auth: true, role: 'user' } },
        { path: 'client/products', name: 'client-products', component: () => import('../views/ProductsView.vue'), meta: { auth: true, role: 'user' } },
        { path: 'client/favorites', name: 'favorites', component: () => import('../views/FavoritesView.vue'), meta: { auth: true, role: 'user' } },
        { path: 'client/cart', name: 'cart', component: () => import('../views/CartView.vue'), meta: { auth: true, role: 'user' } },
        { path: 'client/orders', name: 'client-orders', component: () => import('../views/OrdersView.vue'), meta: { auth: true, role: 'user' } },
        { path: 'client/profile', name: 'client-profile', component: () => import('../views/ProfileView.vue'), meta: { auth: true, role: 'user' } },
        // Admin specific routes
        { path: 'admin', name: 'admin', component: () => import('../views/AdminView.vue'), meta: { auth: true, role: 'admin' } },
        { path: 'admin/products', name: 'admin-products', component: () => import('../views/ProductsView.vue'), meta: { auth: true, role: 'admin' } },
        { path: 'admin/livreurs', name: 'admin-livreurs', component: () => import('../views/AdminLivreursView.vue'), meta: { auth: true, role: 'admin' } },
        { path: 'admin/orders', name: 'admin-orders', component: () => import('../views/OrdersView.vue'), meta: { auth: true, role: 'admin' } },
        { path: 'admin/profile', name: 'admin-profile', component: () => import('../views/ProfileView.vue'), meta: { auth: true, role: 'admin' } },
        // Livreur specific routes
        { path: 'livreur', name: 'livreur', component: () => import('../views/LivreurView.vue'), meta: { auth: true, role: 'livreur' } },
        { path: 'livreur/orders', name: 'livreur-orders', component: () => import('../views/OrdersView.vue'), meta: { auth: true, role: 'livreur' } },
        { path: 'livreur/profile', name: 'livreur-profile', component: () => import('../views/ProfileView.vue'), meta: { auth: true, role: 'livreur' } }
      ]
    }
  ],
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  
  if (!auth.user && localStorage.getItem('isLoggedIn') === 'true') {
    await auth.fetchUser()
  }

  // Global redirection for authenticated users visiting root or general paths
  if (auth.isAuthenticated) {
    if (to.path === '/' || to.name === 'home') {
      if (auth.isAdmin) return next({ name: 'admin' });
      if (auth.isLivreur) return next({ name: 'livreur' });
      if (auth.isUser) return next({ name: 'client-home' });
    }
  }

  if (to.meta.auth && !auth.isAuthenticated) {
    next({ name: 'login' })
  } else if (to.meta.guest && auth.isAuthenticated) {
    // Redirect to role home if they try to access login/register while authenticated
    if (auth.isAdmin) return next({ name: 'admin' });
    if (auth.isLivreur) return next({ name: 'livreur' });
    next({ name: 'home' })
  } else if (to.meta.role && auth.user?.role?.label !== to.meta.role) {
    next({ name: 'home' })
  } else {
    next()
  }
})

export default router
