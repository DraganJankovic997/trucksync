import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useDriverStore = defineStore('driver', () => {
  const driver = ref(null);
  const drivers = ref([]);

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

  async function fetchDispatcherDrivers() {
    try {
      const { data } = await api.get('/dispatcher/drivers');

      drivers.value = data?.data?.drivers ?? [];

      return drivers.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.driver.fetchDispatcherDriversError'));

      console.error(
        'Dispatcher drivers request failed.',
        requestError.response
      );

      return [];
    }
  }

  async function saveDriver(licenseNumber, dispatcherId = null) {
    try {
      const { data } = await api.post('/driver', {
        license_number: licenseNumber,
        dispatcher_id: dispatcherId
      });

      driver.value = data?.data?.driver ?? null;

      toast.success(i18n.global.t('messages.driver.saveSuccess'));

      return driver.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.driver.saveError'));

      console.error('Driver save request failed.', requestError.response);

      return null;
    }
  }

  return {
    driver,
    drivers,
    fetchDispatcherDrivers,
    fetchDriver,
    saveDriver
  };
});
