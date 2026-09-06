import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useRouteStopStore = defineStore('route-stop', () => {
  const routeStop = ref(null);
  const routeStops = ref([]);

  function clearRouteStops() {
    routeStop.value = null;
    routeStops.value = [];
  }

  async function fetchRouteStops(id) {
    try {
      const { data } = await api.get(`/route/route-stops/${id}`);

      routeStops.value = data?.data?.route_stops ?? [];

      return routeStops.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.routeStop.fetchError'));

      console.error('Route stops request failed.', requestError.response);

      return [];
    }
  }

  async function createRouteStop(
    id,
    numberOfTrucks,
    numberOfDrivers,
    services
  ) {
    try {
      const { data } = await api.post('/dispatcher/route/route-stop', {
        route_id: id,
        number_of_trucks: numberOfTrucks,
        number_of_drivers: numberOfDrivers,
        services: services
      });

      await fetchRouteStops(id);

      toast.success(i18n.global.t('messages.routeStop.createSuccess'));

      return routeStop.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.routeStop.createError'));

      console.error('Route stop create request failed.', requestError.response);

      return null;
    }
  }

  return {
    clearRouteStops,
    createRouteStop,
    fetchRouteStops,
    routeStop,
    routeStops
  };
});
