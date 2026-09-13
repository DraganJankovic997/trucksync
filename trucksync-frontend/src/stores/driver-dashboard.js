import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useDriverDashboardStore = defineStore('driver-dashboard', () => {
  const currentRoute = ref(null);
  const nextRouteStop = ref(null);
  const upcomingRoutes = ref([]);
  const currentRouteResponse = ref(null);
  const upcomingRoutesResponse = ref(null);

  async function fetchCurrentRoute() {
    try {
      const { data } = await api.get('/driver/current-route');
      const payload = data?.data ?? {};

      currentRouteResponse.value = data ?? null;
      currentRoute.value = payload.route ?? null;
      nextRouteStop.value = payload.next_route_stop ?? null;

      return currentRouteResponse.value;
    } catch (requestError) {
      toast.error(
        i18n.global.t('messages.driverDashboard.fetchCurrentRouteError')
      );

      console.error(
        'Driver current route request failed.',
        requestError.response
      );

      currentRouteResponse.value = null;
      currentRoute.value = null;
      nextRouteStop.value = null;

      return null;
    }
  }

  async function fetchUpcomingRoutes(limit = 3) {
    try {
      const { data } = await api.get('/driver/routes/upcoming', {
        params: {
          limit: limit
        }
      });
      const payload = data?.data ?? {};

      upcomingRoutesResponse.value = data ?? null;
      upcomingRoutes.value = payload.routes ?? [];

      return upcomingRoutesResponse.value;
    } catch (requestError) {
      toast.error(
        i18n.global.t('messages.driverDashboard.fetchUpcomingRoutesError')
      );

      console.error(
        'Driver upcoming routes request failed.',
        requestError.response
      );

      upcomingRoutesResponse.value = null;
      upcomingRoutes.value = [];

      return null;
    }
  }

  async function fetchDashboardData(limit = 3) {
    const [currentRouteData, upcomingRoutesData] = await Promise.all([
      fetchCurrentRoute(),
      fetchUpcomingRoutes(limit)
    ]);

    return {
      current_route: currentRouteData,
      upcoming_routes: upcomingRoutesData
    };
  }

  return {
    currentRoute,
    currentRouteResponse,
    fetchCurrentRoute,
    fetchDashboardData,
    fetchUpcomingRoutes,
    nextRouteStop,
    upcomingRoutes,
    upcomingRoutesResponse
  };
});
