<script setup>
import { onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import DriverRoutesTable from '@/components/driver-routes/DriverRoutesTable.vue';
import { useDriverStore } from '@/stores/driver.js';
import { useRouteStore } from '@/stores/route.js';

const { t } = useI18n();
const driverStore = useDriverStore();
const routeStore = useRouteStore();
const { routes } = storeToRefs(routeStore);

const isFetching = ref(false);
const hasDispatcher = ref(true);

async function loadRoutes() {
  isFetching.value = true;
  hasDispatcher.value = true;

  try {
    routes.value = [];

    const currentDriver = await driverStore.fetchDriver();

    if (!currentDriver?.dispatcher_id) {
      hasDispatcher.value = false;

      return;
    }

    await routeStore.fetchRoutesForDispatcher(currentDriver.dispatcher_id);
  } finally {
    isFetching.value = false;
  }
}

onMounted(() => {
  void loadRoutes();
});
</script>

<template>
  <q-page
    class="driver-routes-page q-pa-lg"
    :aria-label="t('driverRoutes.title')"
  >
    <div class="driver-routes-shell">
      <header class="row items-start justify-between q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md">
          <p
            class="driver-routes-eyebrow text-caption text-weight-bold q-mb-xs"
          >
            {{ t('driverRoutes.eyebrow') }}
          </p>
          <h1 class="text-h4 text-weight-bold q-my-none">
            {{ t('driverRoutes.title') }}
          </h1>
          <p class="driver-routes-description q-mt-sm q-mb-none">
            {{ t('driverRoutes.description') }}
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

      <q-card
        v-if="!hasDispatcher && !isFetching"
        class="driver-routes-card driver-routes-empty-card"
        bordered
        flat
      >
        <q-card-section
          class="driver-routes-empty-state row items-center justify-center q-gutter-md q-pa-xl"
        >
          <q-icon
            class="driver-routes-empty-icon"
            name="assignment_ind"
            size="34px"
          />
          <div class="column">
            <strong>{{ t('driverRoutes.profileRequired.title') }}</strong>
            <span>{{ t('driverRoutes.profileRequired.description') }}</span>
          </div>
        </q-card-section>
      </q-card>

      <DriverRoutesTable v-else :routes="routes" :loading="isFetching" />
    </div>
  </q-page>
</template>
