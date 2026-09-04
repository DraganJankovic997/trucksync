import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useDriverStore = defineStore('driver', () => {
  const driver = ref(null);

  async function fetchDriver() {
    try {
      const { data } = await api.get('/driver');

      driver.value = data?.data?.driver ?? null;

      return driver.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.driver.fetchError'));

      console.error('Driver request failed.', requestError.response);

      return null;
    }
  }

  async function saveDriver(licenseNumber, dispatcherId = null) {
    try {
      const { data } = await api.post('/driver', {
        license_number: licenseNumber,
        dispatcher_id: dispatcherId
      });

      toast.success(i18n.global.t('messages.driver.saveSuccess'));

      fetchDriver();

      return data?.data?.driver ?? null;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.driver.saveError'));

      console.error('Driver save request failed.', requestError.response);

      return null;
    }
  }

  return {
    driver,
    fetchDriver,
    saveDriver
  };
});
