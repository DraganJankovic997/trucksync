import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useRouteStopUsageStore = defineStore('route-stop-usage', () => {
  const routeStopUsage = ref(null);

  function clearRouteStopUsage() {
    routeStopUsage.value = null;
  }

  async function submitRouteStopUsageReview(
    routeStopId,
    rating = null,
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

      routeStopUsage.value = data?.data?.route_stop_usage ?? null;

      toast.success(i18n.global.t('messages.routeStopUsage.saveSuccess'));

      return routeStopUsage.value;
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
    clearRouteStopUsage,
    routeStopUsage,
    submitRouteStopUsageReview
  };
});
