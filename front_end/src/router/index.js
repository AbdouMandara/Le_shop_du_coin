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
        {
          path: 'client',
          meta: { auth: true, role: 'user' },
          children: [
            {
                path: 'favorites',
                name: 'favorites',
                component: () => import('../views/FavoritesView.vue')
            },
            {
                path: 'cart',
                name: 'cart',
                component: () => import('../views/CartView.vue')
            },
            {
                path: 'profile',
                name: 'client-profile',
                component: () => import('../views/ProfileView.vue')
            }
          ]
        },
        // Admin specific routes
        {
          path: 'admin',
          meta: { auth: true, role: 'admin' },
          children: [
            {
              path: '',
              name: 'admin',
              component: () => import('../views/AdminView.vue')
            },
            {
              path: 'profile',
              name: 'admin-profile',
              component: () => import('../views/ProfileView.vue')
            }
          ]
        },
        // Livreur specific routes
        {
          path: 'livreur',
          meta: { auth: true, role: 'livreur' },
          children: [
            {
              path: '',
              name: 'livreur',
              component: () => import('../views/LivreurView.vue')
            },
            {
              path: 'profile',
              name: 'livreur-profile',
              component: () => import('../views/ProfileView.vue')
            }
          ]
        }
      ]
    }
  ],
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  
  if (!auth.user && auth.isAuthenticated) {
    await auth.fetchUser()
  }

  // Global redirection for authenticated users visiting root or general paths
  if (auth.isAuthenticated) {
    if (to.path === '/' || to.name === 'home') {
      if (auth.isAdmin) return next({ name: 'admin' });
      if (auth.isLivreur) return next({ name: 'livreur' });
      // For Client, we could redirect to a client dashboard or just stay if / is allowed
      // Given "routes begin with /client", let's stay or move to a client specific home if it exists
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
