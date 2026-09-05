import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useRouteStore = defineStore('route', () => {
  const routes = ref([]);

  async function fetchRoutesForDispatcher(dispatcherId) {
    try {
      const { data } = await api.get(`/dispatcher/route/${dispatcherId}`);

      routes.value = data?.data?.routes ?? [];

      return routes.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.route.fetchError'));

      console.error('Dispatcher routes request failed.', requestError.response);

      return [];
    }
  }

  async function createRoute(
    origin,
    destination,
    convoySize,
    startDate,
    endDate,
    plannedTravelDetails = null
  ) {
    try {
      const { data } = await api.post('/dispatcher/route', {
        origin: origin,
        destination: destination,
        planned_travel_details: plannedTravelDetails,
        convoy_size: convoySize,
        start_date: startDate,
        end_date: endDate
      });

      const createdRoute = data?.data?.route ?? null;

      if (createdRoute?.dispatcher_id) {
        await fetchRoutesForDispatcher(createdRoute.dispatcher_id);
      }

      toast.success(i18n.global.t('messages.route.createSuccess'));

      return createdRoute;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.route.createError'));

      console.error('Route create request failed.', requestError.response);

      return null;
    }
  }

  async function closeRoute(routeId) {
    try {
      const { data } = await api.post(`/dispatcher/route/close/${routeId}`);

      const closedRoute = data?.data?.route ?? null;

      if (closedRoute?.dispatcher_id) {
        await fetchRoutesForDispatcher(closedRoute.dispatcher_id);
      }

      toast.success(i18n.global.t('messages.route.closeSuccess'));

      return closedRoute;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.route.closeError'));

      console.error('Route close request failed.', requestError.response);

      return null;
    }
  }

  return {
    routes,
    closeRoute,
    createRoute,
    fetchRoutesForDispatcher
  };
});
