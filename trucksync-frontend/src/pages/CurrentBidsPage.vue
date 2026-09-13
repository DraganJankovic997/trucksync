<script setup>
import { useI18n } from 'vue-i18n';
import BidsTable from '@/components/bidding/BidsTable.vue';

const { t } = useI18n();
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

      <BidsTable status="pending" />
    </div>
  </q-page>
</template>
