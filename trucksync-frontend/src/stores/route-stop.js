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

  async function fetchRouteStop(id) {
    try {
      const { data } = await api.get(`/route-stop/${id}`);

      routeStop.value = data?.data?.route_stop ?? null;

      return routeStop.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.routeStop.fetchError'));

      console.error('Route stop request failed.', requestError.response);

      return null;
    }
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

  async function fetchUnfulfilledRouteStops(
    search = null,
    page = 1,
    perPage = 15,
    sortByKey = 'stop_at',
    sortByOrder = 'desc'
  ) {
    try {
      const { data } = await api.get('/route/route-stops', {
        params: {
          search: search,
          page: page,
          per_page: perPage,
          'sortBy.key': sortByKey,
          'sortBy.order': sortByOrder
        }
      });

      return data ?? null;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.routeStop.fetchError'));

      console.error(
        'Unfulfilled route stops request failed.',
        requestError.response
      );

      return null;
    }
  }

  async function createRouteStop(
    id,
    location,
    description,
    stopAt,
    numberOfTrucks,
    numberOfDrivers,
    services
  ) {
    try {
      const { data } = await api.post('/dispatcher/route/route-stop', {
        route_id: id,
        location: location,
        description: description,
        stop_at: stopAt,
        number_of_trucks: numberOfTrucks,
        number_of_drivers: numberOfDrivers,
        services: services
      });

      routeStop.value = data?.data?.route_stop ?? null;
      await fetchRouteStops(id);

      toast.success(i18n.global.t('messages.routeStop.createSuccess'));

      return routeStop.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.routeStop.createError'));

      console.error('Route stop create request failed.', requestError.response);

      return null;
    }
  }

  async function syncRouteStopServices(id, services) {
    try {
      const { data } = await api.put(
        `/dispatcher/route/route-stop/${id}/services`,
        {
          services: services
        }
      );

      routeStop.value = data?.data?.route_stop ?? null;

      if (routeStop.value?.route_id) {
        await fetchRouteStops(routeStop.value.route_id);
      }

      toast.success(i18n.global.t('messages.routeStop.updateServicesSuccess'));

      return routeStop.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.routeStop.updateServicesError'));

      console.error(
        'Route stop services update request failed.',
        requestError.response
      );

      return null;
    }
  }

  return {
    clearRouteStops,
    createRouteStop,
    fetchRouteStop,
    fetchRouteStops,
    fetchUnfulfilledRouteStops,
    routeStop,
    routeStops,
    syncRouteStopServices
  };
});
