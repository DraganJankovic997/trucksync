import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { ref } from 'vue';

export const useDriverStore = defineStore('driver', () => {
  const driver = ref(null);

  async function fetchDriver() {
    const { data } = await api.get('/driver');

    driver.value = data?.data?.driver ?? null;

    return driver.value;
  }

  async function saveDriver(licenseNumber, dispatcherId) {
    const { data } = await api.post('/driver', {
      license_number: licenseNumber,
      dispatcher_id: dispatcherId
    });

    return data?.data?.driver ?? null;
  }

  return {
    driver,
    fetchDriver,
    saveDriver
  };
});
