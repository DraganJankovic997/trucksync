import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useRestStopStore = defineStore('rest-stop', () => {
  const restStop = ref(null);
  const restStops = ref([]);

  function clearRestStops() {
    restStops.value = [];
  }

  async function fetchAdminRestStops() {
    try {
      const { data } = await api.get('/admin/rest-stops');

      restStops.value = data?.data?.rest_stops ?? [];

      return restStops.value;
    } catch (requestError) {
      restStops.value = [];

      toast.error(i18n.global.t('messages.restStop.fetchAllError'));

      console.error('Admin rest stops request failed.', requestError.response);

      return [];
    }
  }

  async function fetchRestStop() {
    try {
      const { data } = await api.get('/rest-stop');

      restStop.value = data?.data?.rest_stop ?? null;

      return restStop.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.restStop.fetchError'));

      console.error('Rest stop request failed.', requestError.response);

      return null;
    }
  }

  async function saveRestStop(city, address, postCode, worksFrom, worksTo) {
    try {
      const { data } = await api.post('/rest-stop', {
        city: city,
        address: address,
        post_code: postCode,
        works_from: worksFrom,
        works_to: worksTo
      });

      restStop.value = data?.data?.rest_stop ?? null;

      toast.success(i18n.global.t('messages.restStop.saveSuccess'));

      return restStop.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.restStop.saveError'));

      console.error('Rest stop save request failed.', requestError.response);

      return null;
    }
  }

  return {
    clearRestStops,
    fetchAdminRestStops,
    restStop,
    restStops,
    fetchRestStop,
    saveRestStop
  };
});
