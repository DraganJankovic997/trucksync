import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useBidStore = defineStore('bid', () => {
  const bid = ref(null);

  function clearBid() {
    bid.value = null;
  }

  async function fetchBid(routeStopId) {
    try {
      const { data } = await api.get(`/rest-stop/bids/${routeStopId}`);

      bid.value = data?.data?.bid ?? null;

      return bid.value;
    } catch (requestError) {
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
    deleteBid,
    fetchBid,
    saveBid
  };
});
