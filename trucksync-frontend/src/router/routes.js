const routes = [
  {
    path: '/',
    component: () => import('@/layouts/AuthenticatedLayout.vue'),
    children: [
      {
        path: '/',
        name: 'dashboard',
        component: () => import('@/pages/DashboardPage.vue')
      },
      {
        path: '/profile',
        name: 'profile',
        component: () => import('@/pages/ProfilePage.vue'),
        meta: { requiresAuth: true }
      }
    ],
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/',
    component: () => import('@/layouts/UnauthenticatedLayout.vue'),
    children: [
      {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/LoginPage.vue')
      },
      {
        path: '/register',
        name: 'register',
        component: () => import('@/pages/RegisterPage.vue')
      }
    ],
    meta: {
      guestOnly: true
    }
  },
  {
    path: '/500',
    name: 'server-error',
    component: () => import('@/pages/ServerErrorPage.vue')
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('@/pages/ErrorNotFound.vue')
  }
];

export default routes;
