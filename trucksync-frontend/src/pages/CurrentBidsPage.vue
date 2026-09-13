<script setup>
import { useI18n } from 'vue-i18n';
import BidsTable from '@/components/bidding/BidsTable.vue';

const { t } = useI18n();
const todayDate = formatDateParam(new Date());

function formatDateParam(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}
</script>

<template>
  <q-page
    class="current-bids-page q-pa-lg"
    :aria-label="t('currentBids.title')"
  >
    <div class="current-bids-shell">
      <header class="q-mb-lg">
        <p class="current-bids-eyebrow text-caption text-weight-bold q-mb-xs">
          {{ t('currentBids.eyebrow') }}
        </p>
        <h1 class="text-h4 text-weight-bold q-my-none">
          {{ t('currentBids.title') }}
        </h1>
        <p class="current-bids-description q-mt-sm q-mb-none">
          {{ t('currentBids.description') }}
        </p>
      </header>

      <BidsTable status="pending" :from="todayDate" />
    </div>
  </q-page>
</template>
