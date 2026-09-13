<script setup>
import { useI18n } from 'vue-i18n';
import BidsTable from '@/components/bidding/BidsTable.vue';

const { t } = useI18n();
</script>

<template>
  <q-page class="all-bids-page q-pa-lg" :aria-label="t('allBids.title')">
    <div class="all-bids-shell">
      <header class="q-mb-lg">
        <p class="all-bids-eyebrow text-caption text-weight-bold q-mb-xs">
          {{ t('allBids.eyebrow') }}
        </p>
        <h1 class="text-h4 text-weight-bold q-my-none">
          {{ t('allBids.title') }}
        </h1>
        <p class="all-bids-description q-mt-sm q-mb-none">
          {{ t('allBids.description') }}
        </p>
      </header>

      <BidsTable :status="null" />
    </div>
  </q-page>
</template>
