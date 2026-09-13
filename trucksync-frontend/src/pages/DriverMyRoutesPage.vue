<script setup>
import { computed, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import DriverCurrentRouteCard from '@/components/driver-routes/DriverCurrentRouteCard.vue';
import DriverRoutesTable from '@/components/driver-routes/DriverRoutesTable.vue';
import { useRouteStore } from '@/stores/route.js';

const { t } = useI18n();
const routeStore = useRouteStore();
const { routes } = storeToRefs(routeStore);

const isFetching = ref(false);
const todayDateKey = ref(formatLocalDateKey(new Date()));
const currentRoutes = computed(() => routes.value.filter(isCurrentRoute));
const otherRoutes = computed(() =>
  routes.value.filter(route => !isCurrentRoute(route))
);
const tableTitle = computed(() =>
  currentRoutes.value.length
    ? t('driverRoutes.my.table.otherTitle')
    : t('driverRoutes.my.table.title')
);
const tableEmptyTitle = computed(() =>
  currentRoutes.value.length
    ? t('driverRoutes.my.table.emptyOtherTitle')
    : t('driverRoutes.my.table.emptyTitle')
);
const tableEmptyDescription = computed(() =>
  currentRoutes.value.length
    ? t('driverRoutes.my.table.emptyOtherDescription')
    : t('driverRoutes.my.table.emptyDescription')
);

async function loadRoutes() {
  isFetching.value = true;
  todayDateKey.value = formatLocalDateKey(new Date());

  try {
    routes.value = [];
    await routeStore.fetchRoutesForDriver();
  } finally {
    isFetching.value = false;
  }
}

function isCurrentRoute(route) {
  if (route?.closed_at) {
    return false;
  }

  const startDate = dateKeyFromValue(route?.start_date);
  const endDate = dateKeyFromValue(route?.end_date);

  return (
    startDate !== null &&
    endDate !== null &&
    startDate <= todayDateKey.value &&
    todayDateKey.value <= endDate
  );
}

function dateKeyFromValue(value) {
  if (!value) {
    return null;
  }

  if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value)) {
    return value.slice(0, 10);
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return null;
  }

  return formatLocalDateKey(date);
}

function formatLocalDateKey(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

onMounted(() => {
  void loadRoutes();
});
</script>

<template>
  <q-page
    class="driver-routes-page q-pa-lg"
    :aria-label="t('driverRoutes.my.title')"
  >
    <div class="driver-routes-shell">
      <header class="row items-start justify-between q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md">
          <p
            class="driver-routes-eyebrow text-caption text-weight-bold q-mb-xs"
          >
            {{ t('driverRoutes.my.eyebrow') }}
          </p>
          <h1 class="text-h4 text-weight-bold q-my-none">
            {{ t('driverRoutes.my.title') }}
          </h1>
          <p class="driver-routes-description q-mt-sm q-mb-none">
            {{ t('driverRoutes.my.description') }}
          </p>
        </div>

        <div class="col-12 col-md-auto">
          <q-btn
            color="primary"
            icon="refresh"
            outline
            no-caps
            class="text-weight-bold"
            :label="t('driverRoutes.actions.refresh')"
            :loading="isFetching"
            @click="loadRoutes"
          />
        </div>
      </header>

      <div
        v-if="currentRoutes.length"
        class="driver-routes-current-section q-mb-lg"
      >
        <DriverCurrentRouteCard
          v-for="route in currentRoutes"
          :key="route.id"
          :route="route"
          class="q-mb-md"
        />
      </div>

      <DriverRoutesTable
        :routes="otherRoutes"
        :loading="isFetching"
        :title="tableTitle"
        :empty-title="tableEmptyTitle"
        :empty-description="tableEmptyDescription"
      />
    </div>
  </q-page>
</template>
