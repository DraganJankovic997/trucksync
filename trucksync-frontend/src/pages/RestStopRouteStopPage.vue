<script setup>
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute as useRouterRoute, useRouter } from 'vue-router';
import RouteDetailsCard from '@/components/bidding/RouteDetailsCard.vue';
import RouteStopServicesSection from '@/components/bidding/RouteStopServicesSection.vue';
import { useDispatcherStore } from '@/stores/dispatcher.js';
import { useRouteStore } from '@/stores/route.js';
import { useRouteStopStore } from '@/stores/route-stop.js';

const { t } = useI18n();
const routerRoute = useRouterRoute();
const router = useRouter();
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

const dispatcherTitle = computed(() => {
  const dispatcherName =
    dispatcher.value?.company_name ?? routeStop.value?.dispatcher_company_name;
  const registrationNumber =
    dispatcher.value?.registration_number ?? dispatcher.value?.reg_number;

  if (dispatcherName && registrationNumber) {
    return t('bidding.page.title', {
      dispatcherName: dispatcherName,
      registrationNumber: registrationNumber
    });
  }

  return formatValue(dispatcherName ?? registrationNumber);
});

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('bidding.emptyValue')
    : value;
}

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

function goToRouteStops() {
  void router.push({ name: 'route-stops' });
}

onMounted(() => {
  void loadRouteStopDetails();
});
</script>

<template>
  <q-page
    class="bidding-page q-pa-lg"
    :aria-label="t('bidding.page.ariaLabel', { id: routeStopId })"
  >
    <div class="bidding-shell">
      <header class="row items-start justify-between q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md">
          <p class="bidding-eyebrow text-caption text-weight-bold q-mb-xs">
            {{ t('bidding.page.eyebrow') }}
          </p>
          <q-skeleton
            v-if="isFetching && !dispatcher"
            type="text"
            width="320px"
            height="46px"
          />
          <h1 v-else class="text-h4 text-weight-bold q-my-none">
            {{ dispatcherTitle }}
          </h1>
          <p class="bidding-description q-mt-sm q-mb-none">
            {{ t('bidding.page.description') }}
          </p>
        </div>

        <div class="col-12 col-md-auto row q-col-gutter-sm">
          <div class="col-auto">
            <q-btn
              color="primary"
              icon="arrow_back"
              outline
              no-caps
              class="text-weight-bold"
              :label="t('bidding.page.actions.back')"
              @click="goToRouteStops"
            />
          </div>
          <div class="col-auto">
            <q-btn
              color="primary"
              icon="refresh"
              outline
              no-caps
              class="text-weight-bold"
              :label="t('bidding.page.actions.refresh')"
              :loading="isFetching"
              @click="loadRouteStopDetails"
            />
          </div>
        </div>
      </header>

      <q-banner
        v-if="!isFetching && !routeStop"
        class="bidding-unavailable bidding-empty-state q-pa-xl"
      >
        <div class="row items-center q-gutter-md">
          <q-icon class="bidding-empty-icon" name="pin_drop" size="34px" />
          <div class="column">
            <strong>{{ t('bidding.page.emptyTitle') }}</strong>
            <span>{{ t('bidding.page.emptyDescription') }}</span>
          </div>
        </div>
      </q-banner>

      <template v-else>
        <RouteDetailsCard
          class="q-mb-lg"
          :route="routeRecord"
          :loading="isFetching"
        />

        <RouteStopServicesSection
          :route-stop="routeStop"
          :loading="isFetching"
        />
      </template>
    </div>
  </q-page>
</template>
