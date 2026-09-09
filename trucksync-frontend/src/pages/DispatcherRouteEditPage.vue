<script setup>
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
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
  routeRecord.value?.closed_at
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
  }
]);
const isRouteAllowed = ref(false);
const isFetchingRoute = ref(false);
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

function openCreateRouteStopDialog() {
  selectedRouteStop.value = null;
  routeStopDialogOpen.value = true;
}

function openEditRouteStopDialog(routeStop) {
  selectedRouteStop.value = routeStop;
  routeStopDialogOpen.value = true;
}

function openRouteStopBidsDialog(routeStop) {
  if (!routeStop?.id) {
    return;
  }

  selectedBidsRouteStopId.value = routeStop.id;
  routeStopBidsDialogOpen.value = true;
}

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
      <header class="dispatcher-route-edit-header q-mb-md">
        <q-btn
          flat
          color="primary"
          icon="arrow_back"
          no-caps
          class="text-weight-bold"
          :label="t('dispatcherRouteEdit.actions.back')"
          @click="goToRoutes"
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
              :color="routeRecord.closed_at ? 'grey-8' : 'positive'"
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
