<script setup>
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { useRoute as useRouterRoute } from 'vue-router';
import { useDispatcherStore } from '@/stores/dispatcher.js';
import { useRouteStore } from '@/stores/route.js';
import { useRouteStopStore } from '@/stores/route-stop.js';

const routerRoute = useRouterRoute();
const dispatcherStore = useDispatcherStore();
const routeStore = useRouteStore();
const routeStopStore = useRouteStopStore();
const { dispatchers } = storeToRefs(dispatcherStore);
const { route: routeRecord } = storeToRefs(routeStore);
const { routeStop } = storeToRefs(routeStopStore);

const isFetching = ref(false);

const routeStopId = computed(() =>
  Array.isArray(routerRoute.params.id)
    ? routerRoute.params.id[0]
    : routerRoute.params.id
);

const dispatcher = computed(
  () =>
    dispatchers.value.find(
      dispatcherRecord =>
        String(dispatcherRecord.id) === String(routeRecord.value?.dispatcher_id)
    ) ?? null
);

const pageData = computed(() => ({
  route_stop: routeStop.value,
  route: routeRecord.value,
  dispatcher: dispatcher.value
}));

const formattedPageData = computed(() =>
  JSON.stringify(pageData.value, null, 2)
);

async function loadRouteStopDetails() {
  isFetching.value = true;
  routeStop.value = null;
  routeRecord.value = null;
  dispatchers.value = [];

  try {
    const currentRouteStop = await routeStopStore.fetchRouteStop(
      routeStopId.value
    );

    if (!currentRouteStop?.route_id) {
      return;
    }

    const currentRoute = await routeStore.fetchRoute(currentRouteStop.route_id);

    if (!currentRoute?.dispatcher_id) {
      return;
    }

    await dispatcherStore.fetchDispatchers();
  } finally {
    isFetching.value = false;
  }
}

onMounted(() => {
  void loadRouteStopDetails();
});
</script>

<template>
  <q-page class="q-pa-lg">
    <pre>{{ formattedPageData }}</pre>
  </q-page>
</template>
