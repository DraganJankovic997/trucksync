import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useCountryStore = defineStore('country', () => {
  const countries = ref([]);

  async function fetchCountries() {
    try {
      const { data } = await api.get('/countries');

      countries.value = data?.data?.countries;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.country.fetchError'));

      console.error('Countries request failed.', requestError.response);
    }
  }

  return {
    countries,
    fetchCountries
  };
});
