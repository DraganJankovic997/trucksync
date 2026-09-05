import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useRestStopServiceStore = defineStore('rest-stop-service', () => {
  const restStopId = ref(null);
  const restStopService = ref(null);
  const services = ref([]);

  function clearRestStopServices() {
    restStopId.value = null;
    restStopService.value = null;
    services.value = [];
  }

  async function fetchRestStopServices(id) {
    try {
      const { data } = await api.get(`/rest-stop/services/${id}`);

      restStopId.value = id;
      services.value = data?.data?.services ?? [];

      return services.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.restStopService.fetchError'));

      console.error(
        'Rest stop services request failed.',
        requestError.response
      );

      return [];
    }
  }

  async function addRestStopService(serviceId) {
    try {
      const { data } = await api.post('/rest-stop/services/add', {
        service_id: serviceId
      });

      restStopService.value = data?.data?.rest_stop_service ?? null;

      if (restStopId.value) {
        await fetchRestStopServices(restStopId.value);
      }

      toast.success(i18n.global.t('messages.restStopService.addSuccess'));

      return restStopService.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.restStopService.addError'));

      console.error(
        'Rest stop service add request failed.',
        requestError.response
      );

      return null;
    }
  }

  async function removeRestStopService(serviceId) {
    try {
      const { data } = await api.post('/rest-stop/services/remove', {
        service_id: serviceId
      });

      restStopService.value = data?.data?.rest_stop_service ?? null;

      if (restStopId.value) {
        await fetchRestStopServices(restStopId.value);
      }

      toast.success(i18n.global.t('messages.restStopService.removeSuccess'));

      return restStopService.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.restStopService.removeError'));

      console.error(
        'Rest stop service remove request failed.',
        requestError.response
      );

      return null;
    }
  }

  return {
    addRestStopService,
    clearRestStopServices,
    fetchRestStopServices,
    removeRestStopService,
    restStopId,
    restStopService,
    services
  };
});
