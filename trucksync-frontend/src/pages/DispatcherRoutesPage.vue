<script setup>
import { onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import DispatcherRoutesTable from '@/components/dispatcher-routes/DispatcherRoutesTable.vue';
import { useDispatcherStore } from '@/stores/dispatcher.js';
import { useRouteStore } from '@/stores/route.js';

const { t } = useI18n();
const dispatcherStore = useDispatcherStore();
const routeStore = useRouteStore();
const { routes } = storeToRefs(routeStore);

const isFetching = ref(false);

async function loadRoutes() {
  isFetching.value = true;

  try {
    routes.value = [];

    const currentDispatcher = await dispatcherStore.fetchDispatcher();

    if (!currentDispatcher?.id) {
      return;
    }

    await routeStore.fetchRoutesForDispatcher(currentDispatcher.id);
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
    class="dispatcher-routes-page q-pa-lg"
    :aria-label="t('dispatcherRoutes.title')"
  >
    <div class="dispatcher-routes-shell">
      <header class="row items-start justify-between q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md">
          <p
            class="dispatcher-routes-eyebrow text-caption text-weight-bold q-mb-xs"
          >
            {{ t('dispatcherRoutes.eyebrow') }}
          </p>
          <h1 class="text-h4 text-weight-bold q-my-none">
            {{ t('dispatcherRoutes.title') }}
          </h1>
        </div>

        <div class="col-12 col-md-auto">
          <q-btn
            color="primary"
            icon="refresh"
            outline
            no-caps
            class="text-weight-bold"
            :label="t('dispatcherRoutes.actions.refresh')"
            :loading="isFetching"
            @click="loadRoutes"
          />
        </div>
      </header>

      <DispatcherRoutesTable :routes="routes" :loading="isFetching" />
    </div>
  </q-page>
</template>
