<script setup>
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.js';

const props = defineProps({
  route: {
    type: Object,
    required: true
  }
});

const { t } = useI18n();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const sortedRouteStops = computed(() =>
  [...(props.route?.route_stops ?? [])].sort(compareRouteStops)
);
const nextRouteStop = computed(
  () =>
    sortedRouteStops.value.find(routeStop => !isStopFulfilled(routeStop)) ??
    null
);
const assignedDriverCount = computed(() => props.route?.drivers?.length ?? 0);
const currentDriver = computed(() => {
  const userId = user.value?.id;

  if (!userId) {
    return null;
  }

  return (
    props.route?.drivers?.find(
      driver => Number(driver.user_id) === Number(userId)
    ) ?? null
  );
});
const currentDriverRole = computed(() => {
  if (!currentDriver.value) {
    return null;
  }

  return currentDriver.value.is_convoy_leader
    ? t('driverRoutes.current.roles.convoyLeader')
    : t('driverRoutes.current.roles.driver');
});
const routeTitle = computed(() =>
  t('driverRoutes.current.routeTitle', {
    origin: formatValue(props.route.origin),
    destination: formatValue(props.route.destination)
  })
);
const routeDetails = computed(() =>
  [
    {
      icon: 'event',
      label: t('driverRoutes.current.fields.schedule'),
      value: t('driverRoutes.current.dateRange', {
        start: formatDate(props.route.start_date),
        end: formatDate(props.route.end_date)
      })
    },
    {
      icon: 'groups',
      label: t('driverRoutes.current.fields.convoySize'),
      value: formatValue(props.route.convoy_size)
    },
    {
      icon: 'badge',
      label: t('driverRoutes.current.fields.assignedDrivers'),
      value: assignedDriverCount.value
    },
    {
      icon: 'military_tech',
      label: t('driverRoutes.current.fields.yourRole'),
      value: currentDriverRole.value
    }
  ].filter(detail => detail.value !== null)
);

function compareRouteStops(firstRouteStop, secondRouteStop) {
  const firstTimestamp = timestampForRouteStop(firstRouteStop);
  const secondTimestamp = timestampForRouteStop(secondRouteStop);

  if (firstTimestamp !== secondTimestamp) {
    return firstTimestamp - secondTimestamp;
  }

  return Number(firstRouteStop.id ?? 0) - Number(secondRouteStop.id ?? 0);
}

function timestampForRouteStop(routeStop) {
  const date = parseDateTime(routeStop?.stop_at);

  return date ? date.getTime() : Number.MAX_SAFE_INTEGER;
}

function parseDateTime(value) {
  if (!value) {
    return null;
  }

  const date = new Date(value);

  return Number.isNaN(date.getTime()) ? null : date;
}

function isStopFulfilled(routeStop) {
  return Boolean(routeStop?.fulfilled_at);
}

function isNextRouteStop(routeStop) {
  return (
    nextRouteStop.value !== null &&
    Number(routeStop.id) === Number(nextRouteStop.value.id)
  );
}

function stopIcon(routeStop) {
  if (isNextRouteStop(routeStop)) {
    return 'flag';
  }

  return isStopFulfilled(routeStop) ? 'check_circle' : 'trip_origin';
}

function stopStatusLabel(routeStop) {
  if (isNextRouteStop(routeStop)) {
    return t('driverRoutes.current.nextStop');
  }

  return isStopFulfilled(routeStop)
    ? t('driverRoutes.current.stopFulfilled')
    : t('driverRoutes.current.stopPending');
}

function stopStatusColor(routeStop) {
  if (isNextRouteStop(routeStop)) {
    return 'primary';
  }

  return isStopFulfilled(routeStop) ? 'positive' : 'grey-7';
}

function stopTitle(routeStop) {
  return (
    routeStop?.location ||
    t('driverRoutes.current.unnamedStop', {
      id: routeStop?.id ?? t('driverRoutes.current.emptyValue')
    })
  );
}

function serviceLabel(service) {
  const unit = service.measurement_unit ? ` ${service.measurement_unit}` : '';

  return `${service.name} - ${service.quantity}${unit}`;
}

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('driverRoutes.current.emptyValue')
    : value;
}

function formatDate(value) {
  if (!value) {
    return t('driverRoutes.current.emptyValue');
  }

  return new Intl.DateTimeFormat(undefined, {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).format(new Date(`${value}T00:00:00`));
}

function formatDateTime(value) {
  const date = parseDateTime(value);

  if (!date) {
    return t('driverRoutes.current.emptyValue');
  }

  return new Intl.DateTimeFormat(undefined, {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
}
</script>

<template>
  <q-card class="driver-routes-card driver-routes-current-card" bordered flat>
    <q-card-section
      class="driver-routes-current-header row items-start justify-between q-col-gutter-lg q-pa-lg"
    >
      <div class="col-12 col-md">
        <q-badge class="driver-routes-current-badge q-mb-sm" color="primary">
          {{ t('driverRoutes.current.badge') }}
        </q-badge>
        <h2
          class="driver-routes-current-title text-h5 text-weight-bold q-my-none"
        >
          {{ routeTitle }}
        </h2>
        <p class="driver-routes-current-summary q-mt-sm q-mb-none">
          {{ formatValue(props.route.planned_travel_details) }}
        </p>
      </div>

      <div v-if="nextRouteStop" class="col-12 col-md-auto">
        <div class="driver-routes-next-stop-panel">
          <span class="driver-routes-next-stop-label">
            {{ t('driverRoutes.current.nextStop') }}
          </span>
          <strong>{{ stopTitle(nextRouteStop) }}</strong>
          <span>{{ formatDateTime(nextRouteStop.stop_at) }}</span>
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pa-lg">
      <div class="driver-routes-current-details">
        <div
          v-for="detail in routeDetails"
          :key="detail.label"
          class="driver-routes-current-detail"
        >
          <q-icon :name="detail.icon" size="22px" />
          <div>
            <span>{{ detail.label }}</span>
            <strong>{{ detail.value }}</strong>
          </div>
        </div>
      </div>
    </q-card-section>

    <q-card-section class="q-pa-lg q-pt-none">
      <div class="row items-center justify-between q-col-gutter-sm q-mb-md">
        <div class="col">
          <h3 class="text-subtitle1 text-weight-bold q-my-none">
            {{ t('driverRoutes.current.stopsTitle') }}
          </h3>
        </div>
        <div class="col-auto driver-routes-muted text-caption text-weight-bold">
          {{
            t('driverRoutes.current.stopCount', {
              count: sortedRouteStops.length
            })
          }}
        </div>
      </div>

      <q-list
        v-if="sortedRouteStops.length"
        class="driver-routes-stop-list"
        bordered
        separator
      >
        <q-item
          v-for="routeStop in sortedRouteStops"
          :key="routeStop.id"
          class="driver-routes-stop-item"
          :class="{
            'is-next': isNextRouteStop(routeStop),
            'is-fulfilled': isStopFulfilled(routeStop)
          }"
        >
          <q-item-section avatar>
            <q-icon :name="stopIcon(routeStop)" size="26px" />
          </q-item-section>

          <q-item-section>
            <q-item-label class="driver-routes-stop-title">
              <span>{{ stopTitle(routeStop) }}</span>
              <q-badge
                class="driver-routes-stop-status"
                :color="stopStatusColor(routeStop)"
                outline
              >
                {{ stopStatusLabel(routeStop) }}
              </q-badge>
            </q-item-label>
            <q-item-label caption>
              {{ formatDateTime(routeStop.stop_at) }}
            </q-item-label>
            <q-item-label
              v-if="routeStop.description"
              class="driver-routes-stop-description q-mt-xs"
            >
              {{ routeStop.description }}
            </q-item-label>
            <q-item-label class="driver-routes-stop-capacity q-mt-xs">
              {{
                t('driverRoutes.current.stopCapacity', {
                  trucks: formatValue(routeStop.number_of_trucks),
                  drivers: formatValue(routeStop.number_of_drivers)
                })
              }}
            </q-item-label>
            <div
              v-if="routeStop.services?.length"
              class="driver-routes-stop-services q-mt-sm"
            >
              <q-chip
                v-for="service in routeStop.services"
                :key="service.id"
                class="driver-routes-stop-service"
                dense
                square
              >
                {{ serviceLabel(service) }}
              </q-chip>
            </div>
          </q-item-section>
        </q-item>
      </q-list>

      <div
        v-else
        class="driver-routes-empty-state driver-routes-stops-empty row items-center justify-center q-gutter-md q-pa-lg"
      >
        <q-icon
          class="driver-routes-empty-icon"
          name="location_off"
          size="30px"
        />
        <div class="column">
          <strong>{{ t('driverRoutes.current.emptyStopsTitle') }}</strong>
          <span>{{ t('driverRoutes.current.emptyStopsDescription') }}</span>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
