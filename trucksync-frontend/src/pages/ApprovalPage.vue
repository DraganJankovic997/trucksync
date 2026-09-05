<script setup>
import { onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import DispatcherApprovalTable from '@/components/approval/DispatcherApprovalTable.vue';
import RestStopApprovalTable from '@/components/approval/RestStopApprovalTable.vue';
import { useApprovalStore } from '@/stores/approval.js';

const { t } = useI18n();
const approvalStore = useApprovalStore();
const { dispatchers, restStops } = storeToRefs(approvalStore);

const isFetching = ref(false);
const approvingUserId = ref(null);

async function loadProfilesForApproval() {
  isFetching.value = true;

  try {
    await approvalStore.fetchProfilesForApproval();
  } finally {
    isFetching.value = false;
  }
}

onMounted(() => {
  void loadProfilesForApproval();
});

async function handleApprove(profile) {
  if (profile?.user_id === undefined || profile?.user_id === null) {
    return;
  }

  approvingUserId.value = profile.user_id;

  try {
    await approvalStore.approveProfile(profile.user_id);
  } finally {
    approvingUserId.value = null;
  }
}
</script>

<template>
  <q-page class="approval-page q-pa-lg">
    <div class="approval-shell">
      <header class="row items-start justify-between q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md">
          <p class="approval-eyebrow text-caption text-weight-bold q-mb-xs">
            {{ t('approval.eyebrow') }}
          </p>
          <h1 class="text-h4 text-weight-bold q-my-none">
            {{ t('approval.title') }}
          </h1>
        </div>

        <div class="col-12 col-md-auto">
          <q-btn
            color="primary"
            icon="refresh"
            outline
            no-caps
            class="text-weight-bold"
            :label="t('approval.actions.refresh')"
            :loading="isFetching"
            @click="loadProfilesForApproval"
          />
        </div>
      </header>

      <div class="column q-gutter-lg">
        <DispatcherApprovalTable
          :dispatchers="dispatchers"
          :loading="isFetching"
          :approving-user-id="approvingUserId"
          @approve="handleApprove"
        />

        <RestStopApprovalTable
          :rest-stops="restStops"
          :loading="isFetching"
          :approving-user-id="approvingUserId"
          @approve="handleApprove"
        />
      </div>
    </div>
  </q-page>
</template>
