import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { i18n } from 'src/boot/i18n';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useApprovalStore = defineStore('approval', () => {
  const dispatchers = ref([]);
  const restStops = ref([]);

  async function fetchProfilesForApproval() {
    try {
      const { data } = await api.get('/admin/approve');

      dispatchers.value = data?.data?.dispatchers ?? [];
      restStops.value = data?.data?.rest_stops ?? [];

      return {
        dispatchers: dispatchers.value,
        restStops: restStops.value
      };
    } catch (requestError) {
      toast.error(i18n.global.t('messages.approval.fetchError'));

      console.error(
        'Profiles pending approval request failed.',
        requestError.response
      );

      return {
        dispatchers: [],
        restStops: []
      };
    }
  }

  async function approveProfile(userId) {
    try {
      const { data } = await api.post(`/admin/approve/${userId}`);
      const approval = data?.data?.approval ?? null;

      await fetchProfilesForApproval();

      toast.success(i18n.global.t('messages.approval.approveSuccess'));

      return approval;
    } catch (requestError) {
      toast.error(i18n.global.t('messages.approval.approveError'));

      console.error('Profile approval request failed.', requestError.response);

      return null;
    }
  }

  return {
    approveProfile,
    dispatchers,
    fetchProfilesForApproval,
    restStops
  };
});
