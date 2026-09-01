import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from '@/i18n/instance.js';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

const TOKEN_STORAGE_KEY = 'token';

function message(key) {
  return i18n.global.t(`messages.auth.${key}`);
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);

  function getToken() {
    if (typeof window === 'undefined') {
      return null;
    }

    return window.localStorage.getItem(TOKEN_STORAGE_KEY);
  }

  function storeToken(token) {
    if (typeof window === 'undefined') {
      return;
    }

    window.localStorage.setItem(TOKEN_STORAGE_KEY, token);
  }

  function clearToken() {
    if (typeof window === 'undefined') {
      return;
    }

    window.localStorage.removeItem(TOKEN_STORAGE_KEY);
  }

  async function register(payload) {
    try {
      const { data } = await api.post('/auth/register', payload);

      user.value = data?.data?.user ?? null;
      toast.success(message('registerSuccess'));

      return data?.data ?? null;
    } catch (requestError) {
      toast.error(message('registerError'));

      console.error('Registration request failed.', requestError.response);

      return null;
    }
  }

  async function login(email, password, password_confirmation = null) {
    try {
      const { data } = await api.post('/auth/login', {
        email: email,
        password: password,
        password_confirmation: password_confirmation
      });
      const authToken = data?.data?.token ?? null;

      if (!authToken) {
        clearSession();
        toast.error(message('loginError'));

        return null;
      }

      user.value = null;
      storeToken(authToken);
      toast.success(message('loginSuccess'));

      return authToken;
    } catch (requestError) {
      clearSession();
      toast.error(message('loginError'));

      console.error('Login request failed.', requestError.response);

      return null;
    }
  }

  async function me() {
    const token = getToken();

    if (!token) {
      clearSession();
      return null;
    }

    try {
      const { data } = await api.get('/auth/me', {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });

      user.value = data?.data?.user ?? null;

      return user.value;
    } catch (requestError) {
      clearSession();
      toast.error(message('sessionExpired'));

      console.error(
        'Authenticated user request failed.',
        requestError.response
      );

      return null;
    }
  }

  async function logout() {
    const token = getToken();

    if (!token) {
      clearSession();

      return true;
    }

    try {
      await api.post(
        '/auth/logout',
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`
          }
        }
      );
      toast.success(message('logoutSuccess'));

      return true;
    } catch (requestError) {
      if (requestError.response?.status !== 401) {
        toast.error(message('logoutError'));
      }

      console.error('Logout request failed.', requestError.response);

      return false;
    } finally {
      clearSession();
    }
  }

  function clearSession() {
    user.value = null;
    clearToken();
  }

  return {
    clearSession,
    getToken,
    login,
    logout,
    me,
    user,
    register
  };
});
