<script setup>
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { toast } from '@/boot/toast.js';
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
const routeStatus = computed(() =>
  routeRecord.value?.closed_at
    ? t('dispatcherRouteEdit.details.closed')
    : t('dispatcherRouteEdit.details.open')
);
const routeDetails = computed(() => [
  {
    key: 'origin',
    label: t('dispatcherRouteEdit.details.origin'),
    value: formatValue(routeRecord.value?.origin)
  },
  {
    key: 'destination',
    label: t('dispatcherRouteEdit.details.destination'),
    value: formatValue(routeRecord.value?.destination)
  },
  {
    key: 'convoySize',
    label: t('dispatcherRouteEdit.details.convoySize'),
    value: formatValue(routeRecord.value?.convoy_size)
  },
  {
    key: 'startDate',
    label: t('dispatcherRouteEdit.details.startDate'),
    value: formatDate(routeRecord.value?.start_date)
  },
  {
    key: 'endDate',
    label: t('dispatcherRouteEdit.details.endDate'),
    value: formatDate(routeRecord.value?.end_date)
  }
]);
const isRouteAllowed = ref(false);
const isFetchingRoute = ref(false);

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

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('dispatcherRouteEdit.details.emptyValue')
    : value;
}

function formatDate(value) {
  if (!value) {
    return t('dispatcherRouteEdit.details.emptyValue');
  }

  return new Intl.DateTimeFormat(undefined, {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).format(new Date(`${value}T00:00:00`));
}

function goToRoutes() {
  void router.push({ name: 'dispatcher-routes' });
}

onMounted(() => {
  void validateRouteOwnership();
});
</script>

<template>
  <q-page
    v-if="isRouteAllowed && routeRecord"
    class="dispatcher-route-edit-page q-pa-lg"
    :aria-label="t('dispatcherRouteEdit.title', { route_id: routeId })"
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
              {{ t('dispatcherRouteEdit.title', { route_id: routeId }) }}
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
          <div
            v-for="detail in routeDetails"
            :key="detail.key"
            class="dispatcher-route-edit-detail"
          >
            <span class="dispatcher-route-edit-detail-label">
              {{ detail.label }}
            </span>
            <strong>{{ detail.value }}</strong>
          </div>

          <div
            class="dispatcher-route-edit-detail dispatcher-route-edit-detail-wide"
          >
            <span class="dispatcher-route-edit-detail-label">
              {{ t('dispatcherRouteEdit.details.plannedTravelDetails') }}
            </span>
            <strong>{{
              formatValue(routeRecord.planned_travel_details)
            }}</strong>
          </div>
        </div>
      </section>

      <DispatcherRouteStopsTable
        :route-stops="routeStops"
        :loading="isFetchingRoute"
      />
    </div>
  </q-page>
</template>
