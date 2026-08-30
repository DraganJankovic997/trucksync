const routes = [
  {
    path: '/',
    component: () => import('@/layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('@/pages/IndexPage.vue') },
      {
        path: 'login',
        name: 'login',
        component: () => import('@/pages/LoginPage.vue')
      },
      { path: 'register', component: () => import('@/pages/RegisterPage.vue') },
      {
        path: '500',
        name: 'server-error',
        component: () => import('@/pages/ServerErrorPage.vue')
      },
      { path: 'second', component: () => import('@/pages/SecondPage.vue') }
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('@/pages/ErrorNotFound.vue')
  }
];

export default routes;
