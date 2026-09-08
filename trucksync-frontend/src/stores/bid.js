import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useBidStore = defineStore('bid', () => {
  const bid = ref(null);
  const routeStopBids = ref([]);

  function clearBid() {
    bid.value = null;
  }

  function clearRouteStopBids() {
    routeStopBids.value = [];
  }

  async function fetchDispatcherRouteStopBids(routeStopId) {
    try {
      const { data } = await api.get(
        `/dispatcher/route/route-stop/${routeStopId}/bids`
      );

      routeStopBids.value = data?.data?.bids ?? [];

      return routeStopBids.value;
    } catch (requestError) {
      routeStopBids.value = [];

      toast.error(i18n.global.t('messages.bid.fetchRouteStopBidsError'));

      console.error(
        'Dispatcher route stop bids request failed.',
        requestError.response
      );

      return [];
    }
  }

  async function fetchBid(routeStopId, options = {}) {
    try {
      const { data } = await api.get(`/rest-stop/bids/${routeStopId}`);

      bid.value = data?.data?.bid ?? null;

      return bid.value;
    } catch (requestError) {
      bid.value = null;

      if (options.silentNotFound && requestError.response?.status === 404) {
        return null;
      }

      toast.error(i18n.global.t('messages.bid.fetchError'));

      console.error('Bid request failed.', requestError.response);

      return null;
    }
  }

  async function saveBid(routeStopId, originalPrice, price) {
    try {
      const { data } = await api.post('/rest-stop/bids', {
        route_stop_id: routeStopId,
        original_price: originalPrice,
        price: price
      });

      bid.value = data?.data?.bid ?? null;

      toast.success(i18n.global.t('messages.bid.saveSuccess'));

      return bid.value;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.bid.saveError'));

      console.error('Bid save request failed.', requestError.response);

      return null;
    }
  }

  async function deleteBid(routeStopId) {
    try {
      const { data } = await api.delete(`/rest-stop/bids/${routeStopId}`);
      const deletedBid = data?.data?.bid ?? null;

      if (String(bid.value?.route_stop_id) === String(routeStopId)) {
        bid.value = null;
      }

      toast.success(i18n.global.t('messages.bid.deleteSuccess'));

      return deletedBid;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.bid.deleteError'));

      console.error('Bid delete request failed.', requestError.response);

      return null;
    }
  }

  return {
    bid,
    clearBid,
    clearRouteStopBids,
    deleteBid,
    fetchDispatcherRouteStopBids,
    fetchBid,
    routeStopBids,
    saveBid
  };
});
