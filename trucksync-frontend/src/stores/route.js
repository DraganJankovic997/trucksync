import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';

export const useRouteStore = defineStore('route', () => {
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

      toast.success(i18n.global.t('messages.route.createSuccess'));

    } catch (requestError) {
      toast.error(i18n.global.t('messages.route.createError'));

      console.error('Route create request failed.', requestError.response);

      return null;
    }
  }

  async function closeRoute(routeId) {
    try {
      const { data } = await api.post(`/dispatcher/route/close/${routeId}`);

      toast.success(i18n.global.t('messages.route.closeSuccess'));
    } catch (requestError) {
      toast.error(i18n.global.t('messages.route.closeError'));

      console.error('Route close request failed.', requestError.response);

      return null;
    }
  }

  return {
    closeRoute,
    createRoute
  };
});
