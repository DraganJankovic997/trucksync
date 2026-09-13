<script setup>
import { computed, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { useDriverDashboardStore } from '@/stores/driver-dashboard.js';

const { t } = useI18n();
const driverDashboardStore = useDriverDashboardStore();
const { currentRouteResponse, upcomingRoutesResponse } =
  storeToRefs(driverDashboardStore);

const isFetching = ref(false);

const currentRouteJson = computed(() => formatJson(currentRouteResponse.value));
const upcomingRoutesJson = computed(() =>
  formatJson(upcomingRoutesResponse.value)
);

async function loadDashboardData() {
  isFetching.value = true;

  try {
    await driverDashboardStore.fetchDashboardData();
  } finally {
    isFetching.value = false;
  }
}

function formatJson(value) {
  return JSON.stringify(value, null, 2);
}

onMounted(() => {
  void loadDashboardData();
});
</script>

<template>
  <q-page
    class="driver-dashboard-page q-pa-lg"
    :aria-label="t('driverDashboard.title')"
  >
    <div class="driver-dashboard-shell">
      <header class="row items-start justify-between q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md">
          <p
            class="driver-dashboard-eyebrow text-caption text-weight-bold q-mb-xs"
          >
            {{ t('driverDashboard.eyebrow') }}
          </p>
          <h1 class="text-h4 text-weight-bold q-my-none">
            {{ t('driverDashboard.title') }}
          </h1>
          <p class="driver-dashboard-description q-mt-sm q-mb-none">
            {{ t('driverDashboard.description') }}
          </p>
        </div>

        <div class="col-12 col-md-auto">
          <q-btn
            color="primary"
            icon="refresh"
            outline
            no-caps
            class="text-weight-bold"
            :label="t('driverDashboard.actions.refresh')"
            :loading="isFetching"
            @click="loadDashboardData"
          />
        </div>
      </header>

      <div class="driver-dashboard-grid">
        <q-card class="driver-dashboard-json-card" bordered flat>
          <q-card-section>
            <h2 class="text-h6 text-weight-bold q-my-none">
              {{ t('driverDashboard.sections.currentRoute') }}
            </h2>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <pre
              class="driver-dashboard-json"
            ><code>{{ currentRouteJson }}</code></pre>
          </q-card-section>
        </q-card>

        <q-card class="driver-dashboard-json-card" bordered flat>
          <q-card-section>
            <h2 class="text-h6 text-weight-bold q-my-none">
              {{ t('driverDashboard.sections.upcomingRoutes') }}
            </h2>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <pre
              class="driver-dashboard-json"
            ><code>{{ upcomingRoutesJson }}</code></pre>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>
