import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';

export const useUserStore = defineStore('user', () => {
  async function update(payload) {

    try {
      const { data } = await api.put('/user', payload);

      toast.success(i18n.global.t('messages.profile.updateSuccess'));

      return data?.data?.user;
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
