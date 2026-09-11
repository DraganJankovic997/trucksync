<script setup>
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { toast } from '@/boot/toast.js';
import DispatcherRouteDetail from '@/components/dispatcher-routes/DispatcherRouteDetail.vue';
import DispatcherRouteStopBidsDialog from '@/components/dispatcher-routes/DispatcherRouteStopBidsDialog.vue';
import DispatcherRouteStopDialog from '@/components/dispatcher-routes/DispatcherRouteStopDialog.vue';
import DispatcherRouteStopsTable from '@/components/dispatcher-routes/DispatcherRouteStopsTable.vue';
import { useAuthStore } from '@/stores/auth.js';
import { useDispatcherStore } from '@/stores/dispatcher.js';
import { useRouteStore } from '@/stores/route.js';

const { t } = useI18n();
const routerRoute = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const dispatcherStore = useDispatcherStore();
const routeStore = useRouteStore();
const { user } = storeToRefs(authStore);
const { route: routeRecord } = storeToRefs(routeStore);

const routeId = computed(() =>
  Array.isArray(routerRoute.params.routeId)
    ? routerRoute.params.routeId[0]
    : routerRoute.params.routeId
);
const routeStops = computed(() => routeRecord.value?.route_stops ?? []);
const routeClosed = computed(() => Boolean(routeRecord.value?.closed_at));
const acceptedBidsTotal = computed(() =>
  routeStops.value.reduce(
    (total, routeStop) => total + Number(routeStop.accepted_bid_price ?? 0),
    0
  )
);
const formattedAcceptedBidsTotal = computed(() =>
  formatPrice(acceptedBidsTotal.value)
);
const routeTitle = computed(() => {
  const origin = String(routeRecord.value?.origin ?? '').trim();
  const destination = String(routeRecord.value?.destination ?? '').trim();

  if (!origin || !destination) {
    return t('dispatcherRouteEdit.fallbackTitle');
  }

  return t('dispatcherRouteEdit.title', {
    origin: origin,
    destination: destination
  });
});
const routeStatus = computed(() =>
  routeClosed.value
    ? t('dispatcherRouteEdit.details.closed')
    : t('dispatcherRouteEdit.details.open')
);
const routeDetails = computed(() => [
  {
    key: 'origin',
    label: t('dispatcherRouteEdit.details.origin'),
    value: routeRecord.value?.origin
  },
  {
    key: 'destination',
    label: t('dispatcherRouteEdit.details.destination'),
    value: routeRecord.value?.destination
  },
  {
    key: 'convoySize',
    label: t('dispatcherRouteEdit.details.convoySize'),
    value: routeRecord.value?.convoy_size
  },
  {
    key: 'startDate',
    label: t('dispatcherRouteEdit.details.startDate'),
    value: routeRecord.value?.start_date,
    format: 'date'
  },
  {
    key: 'endDate',
    label: t('dispatcherRouteEdit.details.endDate'),
    value: routeRecord.value?.end_date,
    format: 'date'
  },
  {
    key: 'acceptedBidsTotal',
    label: t('dispatcherRouteEdit.details.acceptedBidsTotal'),
    value: formattedAcceptedBidsTotal.value
  }
]);
const isRouteAllowed = ref(false);
const isFetchingRoute = ref(false);
const isClosingRoute = ref(false);
const routeStopDialogOpen = ref(false);
const routeStopBidsDialogOpen = ref(false);
const selectedRouteStop = ref(null);
const selectedBidsRouteStopId = ref(null);

async function redirectToDashboardWithEditError() {
  await router.replace({ name: 'dashboard' });
  toast.error(t('messages.route.editForbidden'));
}

async function validateRouteOwnership() {
  isFetchingRoute.value = true;

  try {
    if (user.value?.profile_type !== 'dispatcher') {
      await redirectToDashboardWithEditError();
      return;
    }

    const [currentDispatcher, currentRoute] = await Promise.all([
      dispatcherStore.fetchDispatcher(),
      routeStore.fetchRoute(routeId.value)
    ]);

    if (!currentDispatcher?.id) {
      await redirectToDashboardWithEditError();
      return;
    }

    const ownsRoute =
      String(currentRoute?.dispatcher_id) === String(currentDispatcher.id);

    if (!ownsRoute) {
      await redirectToDashboardWithEditError();
      return;
    }

    isRouteAllowed.value = true;
  } finally {
    isFetchingRoute.value = false;
  }
}

function goToRoutes() {
  void router.push({ name: 'dispatcher-routes' });
}

function formatPrice(value) {
  const numberValue = Number(value);

  if (!Number.isFinite(numberValue)) {
    return t('dispatcherRouteEdit.details.emptyValue');
  }

  return new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numberValue);
}

async function closeDispatcherRoute() {
  if (!routeId.value || routeClosed.value || isClosingRoute.value) {
    return;
  }

  isClosingRoute.value = true;

  try {
    const closedRoute = await routeStore.closeRoute(routeId.value);

    if (closedRoute) {
      await routeStore.fetchRoute(routeId.value);
    }
  } finally {
    isClosingRoute.value = false;
  }
}

function openCreateRouteStopDialog() {
  if (routeClosed.value) {
    return;
  }

  selectedRouteStop.value = null;
  routeStopDialogOpen.value = true;
}

function openEditRouteStopDialog(routeStop) {
  if (routeClosed.value) {
    return;
  }

  selectedRouteStop.value = routeStop;
  routeStopDialogOpen.value = true;
}

function openRouteStopBidsDialog(routeStop) {
  if (!routeStop?.id || routeClosed.value) {
    return;
  }

  selectedBidsRouteStopId.value = routeStop.id;
  routeStopBidsDialogOpen.value = true;
}

watch(routeClosed, closed => {
  if (!closed) {
    return;
  }

  routeStopDialogOpen.value = false;
  routeStopBidsDialogOpen.value = false;
  selectedRouteStop.value = null;
  selectedBidsRouteStopId.value = null;
});

onMounted(() => {
  void validateRouteOwnership();
});
</script>

<template>
  <q-page
    v-if="isRouteAllowed && routeRecord"
    class="dispatcher-route-edit-page q-pa-lg"
    :aria-label="routeTitle"
  >
    <div class="dispatcher-route-edit-shell">
      <header
        class="dispatcher-route-edit-header row items-center justify-between q-gutter-sm q-mb-md"
      >
        <q-btn
          flat
          color="primary"
          icon="arrow_back"
          no-caps
          class="text-weight-bold"
          :label="t('dispatcherRouteEdit.actions.back')"
          @click="goToRoutes"
        />
        <q-btn
          color="negative"
          icon="lock"
          no-caps
          class="text-weight-bold"
          :aria-label="t('dispatcherRouteEdit.actions.closeRoute')"
          :disable="isFetchingRoute || isClosingRoute || routeClosed"
          :label="t('dispatcherRouteEdit.actions.closeRoute')"
          :loading="isClosingRoute"
          unelevated
          @click="closeDispatcherRoute"
        />
      </header>

      <section class="dispatcher-route-edit-details q-pa-lg q-mb-lg">
        <div class="row items-start justify-between q-col-gutter-md q-mb-lg">
          <div class="col-12 col-md">
            <p
              class="dispatcher-route-edit-eyebrow text-caption text-weight-bold q-mb-xs"
            >
              {{ t('dispatcherRouteEdit.details.title') }}
            </p>
            <h1 class="text-h4 text-weight-bold q-my-none">
              {{ routeTitle }}
            </h1>
          </div>

          <div class="col-12 col-md-auto">
            <q-badge
              class="dispatcher-route-edit-status"
              :color="routeClosed ? 'grey-8' : 'positive'"
              outline
            >
              {{ routeStatus }}
            </q-badge>
          </div>
        </div>

        <div class="dispatcher-route-edit-details-grid">
          <DispatcherRouteDetail
            v-for="detail in routeDetails"
            :key="detail.key"
            :label="detail.label"
            :value="detail.value"
            :format="detail.format"
          />

          <DispatcherRouteDetail
            :label="t('dispatcherRouteEdit.details.plannedTravelDetails')"
            :value="routeRecord.planned_travel_details"
            wide
          />
        </div>
      </section>

      <DispatcherRouteStopsTable
        :route-stops="routeStops"
        :route-closed="routeClosed"
        :loading="isFetchingRoute"
        @add="openCreateRouteStopDialog"
        @edit="openEditRouteStopDialog"
        @bids="openRouteStopBidsDialog"
      />

      <DispatcherRouteStopDialog
        v-model="routeStopDialogOpen"
        :route-id="routeId"
        :convoy-size="routeRecord.convoy_size"
        :route-stop="selectedRouteStop"
      />

      <DispatcherRouteStopBidsDialog
        v-model="routeStopBidsDialogOpen"
        :route-stop-id="selectedBidsRouteStopId"
      />
    </div>
  </q-page>
</template>
