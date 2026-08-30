import { boot } from 'quasar/wrappers';
import axios from 'axios';

const TOKEN_STORAGE_KEY = 'token';

const api = axios.create({
  baseURL: '/api',
  headers: {
    Accept: 'application/json'
  }
});

function getStoredToken() {
  if (typeof window === 'undefined') {
    return null;
  }

  return window.localStorage.getItem(TOKEN_STORAGE_KEY);
}

function clearStoredToken() {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.removeItem(TOKEN_STORAGE_KEY);
}

function redirectTo(router, routeName) {
  if (router.currentRoute.value.name === routeName) {
    return;
  }

  void router.push({ name: routeName }).catch(() => {});
}

export default boot(({ app, router }) => {
  api.interceptors.request.use(config => {
    const token = getStoredToken();

    if (token) {
      config.headers = config.headers ?? {};
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  });

  api.interceptors.response.use(
    response => response,
    error => {
      const status = error.response?.status;

      if (status === 401) {
        clearStoredToken();
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
