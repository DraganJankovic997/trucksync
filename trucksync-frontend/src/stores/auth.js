import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const

  async function register(payload) {
    try {
      const { data } = await api.post('/auth/register', payload);

      user.value = data?.data?.user ?? null;
      toast.success(i18n.global.t('messages.auth.registerSuccess'));

      return data?.data ?? null;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.auth.registerError'));

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
        toast.error(i18n.global.t('messages.auth.loginError'));

        return null;
      }

      user.value = null;
      localStorage.setItem('token', authToken);
      toast.success(i18n.global.t('messages.auth.loginSuccess'));

      return authToken;
    } catch (requestError) {
      clearSession();
      toast.error(i18n.global.t('messages.auth.loginError'));

      console.error('Login request failed.', requestError.response);

      return null;
    }
  }

  async function me() {
    try {
      const { data } = await api.get('/auth/me');

      user.value = data?.data?.user ?? null;

      return user.value;
    } catch (requestError) {
      clearSession();
      toast.error(i18n.global.t('messages.auth.sessionExpired'));

      console.error(
        'Authenticated user request failed.',
        requestError.response
      );

      return null;
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout');
      toast.success(i18n.global.t('messages.auth.logoutSuccess'));

      return true;
    } catch (requestError) {
      if (requestError.response?.status !== 401) {
        toast.error(i18n.global.t('messages.auth.logoutError'));
      }

      console.error('Logout request failed.', requestError.response);

      return false;
    } finally {
      clearSession();
    }
  }

  function clearSession() {
    user.value = null;
    localStorage.removeItem('token');
  }

  return {
    clearSession,
    login,
    logout,
    me,
    user,
    register
  };
});
