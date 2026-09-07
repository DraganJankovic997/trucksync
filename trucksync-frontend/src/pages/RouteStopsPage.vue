<script setup>
import { useI18n } from 'vue-i18n';
import RouteStopsTable from '@/components/route-stops/RouteStopsTable.vue';

const { t } = useI18n();
</script>

<template>
  <q-page class="route-stops-page q-pa-lg" :aria-label="t('routeStops.title')">
    <div class="route-stops-shell">
      <header class="q-mb-lg">
        <div>
          <p class="route-stops-eyebrow text-caption text-weight-bold q-mb-xs">
            {{ t('routeStops.eyebrow') }}
          </p>
          <h1 class="text-h4 text-weight-bold q-my-none">
            {{ t('routeStops.title') }}
          </h1>
          <p class="route-stops-description q-mt-sm q-mb-none">
            {{ t('routeStops.description') }}
          </p>
        </div>
      </header>

      <RouteStopsTable />
    </div>
  </q-page>
</template>
