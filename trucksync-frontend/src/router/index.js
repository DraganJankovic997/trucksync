import { defineRouter } from '#q-app';
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory
} from 'vue-router';

import { useAuthStore } from '@/stores/auth.js';
import routes from './routes.js';
import { storeToRefs } from 'pinia';

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default defineRouter(({ store }) => {
  const createHistory = import.meta.env.QUASAR_SERVER
    ? createMemoryHistory
    : import.meta.env.QUASAR_VUE_ROUTER_MODE === 'history'
      ? createWebHistory
      : createWebHashHistory;

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(import.meta.env.QUASAR_VUE_ROUTER_BASE)
  });

  Router.beforeEach(async (to, from) => {
    const authStore = useAuthStore(store);
    await authStore.me();
    const { user } = storeToRefs(authStore);

    if (to.meta.requiresAuth === true) {
      console.log('inside if');
      if (!user.value) {
        return {
          name: 'login',
          query: { redirect: to.fullPath }
        };
      }
    } else if (to.meta.guestOnly === true) {
      console.log('inside else if');
      if (user.value) {
                  console.log('inside smaller if');

          return {
            name: 'dashboard',
          };
      }
      console.log('passed the small if');
    }
    console.log('came to the end');
    return true;
  });

  return Router;
});
