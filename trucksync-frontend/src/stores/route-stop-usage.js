import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useRouteStopUsageStore = defineStore('route-stop-usage', () => {
  const routeStopUsages = ref([]);


  async function fetchAdminRatings(
    restStopId = null,
    page = 1,
    perPage = 15,
    reportsOnly = false
  ) {
    const params = {
      page: page,
      per_page: perPage
    };

    if (restStopId !== null) {
      params.rest_stop_id = restStopId;
    }

    if (reportsOnly === true) {
      params.is_report = true;
    }

    try {
      const { data } = await api.get('/admin/ratings', {
        params: params
      });

      routeStopUsages.value = data?.data?.route_stop_usages ?? [];

      return data ?? null;
    } catch (requestError) {
      routeStopUsages.value = [];

      toast.error(i18n.global.t('messages.routeStopUsage.fetchRatingsError'));

      console.error(
        'Route stop usage ratings request failed.',
        requestError.response
      );

      return null;
    }
  }

  async function submitRouteStopUsageReview(
    routeStopId,
    rating,
    report = null,
    isReport = false
  ) {
    try {
      const { data } = await api.post(
        `/driver/route-stop/${routeStopId}/usage-review`,
        {
          rating: rating,
          report: report,
          is_report: isReport
        }
      );

      toast.success(i18n.global.t('messages.routeStopUsage.saveSuccess'));

      return data?.data?.route_stop_usage ?? null;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.routeStopUsage.saveError'));

      console.error(
        'Route stop usage review request failed.',
        requestError.response
      );

      return null;
    }
  }

  return {
    fetchAdminRatings,
    routeStopUsages,
    submitRouteStopUsageReview
  };
});
