import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useDispatcherStore = defineStore('dispatcher', () => {
  const dispatcher = ref(null);
  const dispatchers = ref([]);

  async function fetchDispatcher() {
    try {
      const { data } = await api.get('/dispatcher');

      dispatcher.value = data?.data?.dispatcher ?? null;

      return dispatcher.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.dispatcher.fetchError'));

      console.error('Dispatcher request failed.', requestError.response);

      return null;
    }
  }

  async function fetchDispatchers() {
    try {
      const { data } = await api.get('/dispatchers');

      dispatchers.value = data?.data?.dispatchers ?? [];

      return dispatchers.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.dispatcher.fetchAllError'));

      console.error('Dispatchers request failed.', requestError.response);

      return [];
    }
  }

  async function saveDispatcher(
    companyName,
    country,
    city,
    address,
    postCode,
    registrationNumber
  ) {
    try {
      const { data } = await api.post('/dispatcher', {
        company_name: companyName,
        country: country,
        city: city,
        address: address,
        post_code: postCode,
        registration_number: registrationNumber
      });

      dispatcher.value = data?.data?.dispatcher ?? null;

      toast.success(i18n.global.t('messages.dispatcher.saveSuccess'));

      return dispatcher.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.dispatcher.saveError'));

      console.error('Dispatcher save request failed.', requestError.response);

      return null;
    }
  }

  return {
    dispatcher,
    dispatchers,
    fetchDispatcher,
    fetchDispatchers,
    saveDispatcher
  };
});
