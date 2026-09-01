import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { useAuthStore } from '@/stores/auth.js';

export const useUserStore = defineStore('user', () => {
  async function update(payload) {
    const authStore = useAuthStore();
    const token = authStore.getToken();

    if (!token) {
      authStore.clearSession();

      return null;
    }

    try {
      const { data } = await api.put('/user', payload);

      authStore.user = data?.data?.user ?? null;
      toast.success(i18n.global.t('messages.profile.updateSuccess'));

      return authStore.user;
    } catch (requestError) {
      if (requestError.response?.status === 401) {
        authStore.clearSession();
      }

      toast.error(i18n.global.t('messages.profile.updateError'));

      console.error('User update request failed.', requestError.response);

      return null;
    }
  }

  return {
    update
  };
});
