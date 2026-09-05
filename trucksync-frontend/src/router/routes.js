const routes = [
  {
    path: '/',
    component: () => import('@/layouts/AuthenticatedLayout.vue'),
    children: [
      {
        path: '/',
        redirect: { name: 'dashboard' }
      },
      {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/pages/DashboardPage.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/profile',
        name: 'profile',
        component: () => import('@/pages/ProfilePage.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/profile/services',
        name: 'profile-services',
        component: () => import('@/pages/RestStopServicesPage.vue'),
        meta: { requiresAuth: true, requiresRestStop: true }
      },
      {
        path: '/services',
        redirect: { name: 'admin-services' }
      },
      {
        path: '/admin/services',
        name: 'admin-services',
        component: () => import('@/pages/ServicesPage.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
      }
    ]
  },
  {
    path: '/',
    component: () => import('@/layouts/UnauthenticatedLayout.vue'),
    children: [
      {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/LoginPage.vue'),
        meta: { guestOnly: true }
      },
      {
        path: '/register',
        name: 'register',
        component: () => import('@/pages/RegisterPage.vue'),
        meta: { guestOnly: true }
      }
    ]
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
