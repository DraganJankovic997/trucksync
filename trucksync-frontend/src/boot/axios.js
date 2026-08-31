import { boot } from 'quasar/wrappers';
import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    Accept: 'application/json'
  }
});

function redirectTo(router, routeName) {
  if (router.currentRoute.value.name === routeName) {
    return;
  }

  void router.push({ name: routeName }).catch(() => {});
}

export default boot(({ app, router }) => {
  api.interceptors.response.use(
    response => response,
    error => {
      const status = error.response?.status;

      if (status === 401) {
        redirectTo(router, 'login');
      } else if (status >= 500 && status < 600) {
        redirectTo(router, 'server-error');
      }

      return Promise.reject(error);
    }
  );

  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$api = api;
});

export { api, axios };
