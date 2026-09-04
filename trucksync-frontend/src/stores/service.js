import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useServiceStore = defineStore('service', () => {
  const service = ref(null);
  const services = ref([]);

  async function fetchServices() {
    try {
      const { data } = await api.get('/service');

      services.value = data?.data?.services ?? [];

      return services.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.service.fetchError'));

      console.error('Services request failed.', requestError.response);

      return [];
    }
  }

  async function fetchService(id) {
    try {
      const { data } = await api.get(`/service/${id}`);

      service.value = data?.data?.service ?? null;

      return service.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.service.fetchOneError'));

      console.error('Service request failed.', requestError.response);

      return null;
    }
  }

  async function createService(name) {
    try {
      const { data } = await api.post('/service', {
        name: name
      });

      service.value = data?.data?.service ?? null;

      await fetchServices();

      toast.success(i18n.global.t('messages.service.createSuccess'));

      return service.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.service.createError'));

      console.error('Service create request failed.', requestError.response);

      return null;
    }
  }

  async function deleteService(id) {
    try {
      await api.delete(`/service/${id}`);

      await fetchServices();

      if (String(service.value?.id) === String(id)) {
        service.value = null;
      }

      toast.success(i18n.global.t('messages.service.deleteSuccess'));

      return true;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.service.deleteError'));

      console.error('Service delete request failed.', requestError.response);

      return false;
    }
  }

  return {
    service,
    services,
    createService,
    deleteService,
    fetchService,
    fetchServices
  };
});
