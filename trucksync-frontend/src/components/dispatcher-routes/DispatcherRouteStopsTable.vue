<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  routeStops: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits({
  add: () => true,
  edit: routeStop => routeStop?.id !== undefined && routeStop?.id !== null
});

const { t } = useI18n();
const tablePagination = { rowsPerPage: 0 };

const columns = computed(() => [
  {
    name: 'id',
    label: t('dispatcherRouteEdit.routeStops.table.id'),
    field: 'id',
    align: 'left',
    sortable: true
  },
  {
    name: 'location',
    label: t('dispatcherRouteEdit.routeStops.table.location'),
    field: 'location',
    align: 'left',
    sortable: true
  },
  {
    name: 'stopAt',
    label: t('dispatcherRouteEdit.routeStops.table.stopAt'),
    field: 'stopAt',
    align: 'left',
    sortable: true
  },
  {
    name: 'description',
    label: t('dispatcherRouteEdit.routeStops.table.description'),
    field: 'description',
    align: 'left'
  },
  {
    name: 'numberOfTrucks',
    label: t('dispatcherRouteEdit.routeStops.table.numberOfTrucks'),
    field: 'numberOfTrucks',
    align: 'left',
    sortable: true
  },
  {
    name: 'numberOfDrivers',
    label: t('dispatcherRouteEdit.routeStops.table.numberOfDrivers'),
    field: 'numberOfDrivers',
    align: 'left',
    sortable: true
  },
  {
    name: 'services',
    label: t('dispatcherRouteEdit.routeStops.table.services'),
    field: 'services',
    align: 'left'
  }
]);

const rows = computed(() =>
  props.routeStops.map(routeStop => ({
    id: routeStop.id,
    routeStop: routeStop,
    location: formatValue(routeStop.location),
    stopAt: formatDateTime(routeStop.stop_at),
    description: formatValue(routeStop.description),
    numberOfTrucks: formatValue(routeStop.number_of_trucks),
    numberOfDrivers: formatValue(routeStop.number_of_drivers),
    services: formatServices(routeStop.services)
  }))
);

const routeStopCount = computed(() => props.routeStops.length);

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('dispatcherRouteEdit.routeStops.table.emptyValue')
    : value;
}

function formatServices(services) {
  if (!Array.isArray(services)) {
    return [];
  }

  return services.map(service => ({
    id: service.id,
    label: formatServiceLabel(service)
  }));
}

function formatDateTime(value) {
  if (!value) {
    return formatValue(value);
  }

  const dateValue = new Date(value);

  if (Number.isNaN(dateValue.getTime())) {
    return formatValue(value);
  }

  return new Intl.DateTimeFormat(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(dateValue);
}

function formatServiceLabel(service) {
  const unit = service.measurement_unit ? ` ${service.measurement_unit}` : '';

  return `${formatValue(service.name)}: ${formatValue(service.quantity)}${unit}`;
}

function requestEdit(row) {
  if (props.loading) {
    return;
  }

  emit('edit', row.routeStop);
}
</script>

<template>
  <q-card
    class="dispatcher-route-stops-card dispatcher-route-stops-table-card"
    bordered
    flat
  >
    <q-card-section
      class="row items-center justify-between q-col-gutter-md q-pa-lg q-pb-md"
    >
      <div class="col-12 col-sm">
        <h2 class="text-h6 text-weight-bold q-my-none">
          {{ t('dispatcherRouteEdit.routeStops.title') }}
        </h2>
        <div class="dispatcher-route-stops-muted text-caption text-weight-bold">
          {{
            t('dispatcherRouteEdit.routeStops.stopCount', {
              count: routeStopCount
            })
          }}
        </div>
      </div>

      <div class="col-12 col-sm-auto">
        <q-btn
          color="primary"
          icon="add"
          no-caps
          class="text-weight-bold"
          :label="t('dispatcherRouteEdit.routeStops.actions.add')"
          :disable="props.loading"
          unelevated
          @click="emit('add')"
        />
      </div>
    </q-card-section>

    <q-table
      class="dispatcher-route-stops-table"
      flat
      hide-bottom
      row-key="id"
      :rows="rows"
      :columns="columns"
      :loading="props.loading"
      :pagination="tablePagination"
      @row-click="(_event, row) => requestEdit(row)"
    >
      <template #body-cell-id="scope">
        <q-td :props="scope">
          <div class="text-weight-bold">
            {{ scope.row.id }}
          </div>
        </q-td>
      </template>

      <template #body-cell-location="scope">
        <q-td :props="scope">
          <div class="dispatcher-route-stops-location text-weight-bold">
            {{ scope.row.location }}
          </div>
        </q-td>
      </template>

      <template #body-cell-description="scope">
        <q-td :props="scope">
          <div class="dispatcher-route-stops-description">
            {{ scope.row.description }}
          </div>
        </q-td>
      </template>

      <template #body-cell-services="scope">
        <q-td :props="scope">
          <div
            v-if="scope.row.services.length > 0"
            class="dispatcher-route-stops-services"
          >
            <q-badge
              v-for="service in scope.row.services"
              :key="`${scope.row.id}-${service.id}`"
              class="dispatcher-route-stops-service"
              outline
            >
              {{ service.label }}
            </q-badge>
          </div>
          <span v-else>
            {{ t('dispatcherRouteEdit.routeStops.table.emptyValue') }}
          </span>
        </q-td>
      </template>

      <template #no-data>
        <div
          class="dispatcher-route-stops-empty-state row items-center justify-center q-gutter-md q-pa-xl"
        >
          <q-icon
            class="dispatcher-route-stops-empty-icon"
            name="pin_drop"
            size="34px"
          />
          <div class="column">
            <strong>
              {{ t('dispatcherRouteEdit.routeStops.table.emptyTitle') }}
            </strong>
            <span>
              {{ t('dispatcherRouteEdit.routeStops.table.emptyDescription') }}
            </span>
          </div>
        </div>
      </template>
    </q-table>
  </q-card>
</template>
