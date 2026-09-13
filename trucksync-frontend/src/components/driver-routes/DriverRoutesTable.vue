<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  routes: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const { t } = useI18n();
const tablePagination = { rowsPerPage: 0 };

const columns = computed(() => [
  {
    name: 'id',
    label: t('driverRoutes.table.id'),
    field: 'id',
    align: 'left',
    sortable: true
  },
  {
    name: 'origin',
    label: t('driverRoutes.table.origin'),
    field: 'origin',
    align: 'left',
    sortable: true
  },
  {
    name: 'destination',
    label: t('driverRoutes.table.destination'),
    field: 'destination',
    align: 'left',
    sortable: true
  },
  {
    name: 'convoySize',
    label: t('driverRoutes.table.convoySize'),
    field: 'convoySize',
    align: 'left',
    sortable: true
  },
  {
    name: 'startDate',
    label: t('driverRoutes.table.startDate'),
    field: 'startDate',
    align: 'left',
    sortable: true
  },
  {
    name: 'endDate',
    label: t('driverRoutes.table.endDate'),
    field: 'endDate',
    align: 'left',
    sortable: true
  },
  {
    name: 'status',
    label: t('driverRoutes.table.status'),
    field: 'status',
    align: 'left',
    sortable: true
  },
  {
    name: 'plannedTravelDetails',
    label: t('driverRoutes.table.plannedTravelDetails'),
    field: 'plannedTravelDetails',
    align: 'left'
  }
]);

const rows = computed(() =>
  props.routes.map(route => ({
    id: route.id,
    origin: formatValue(route.origin),
    destination: formatValue(route.destination),
    convoySize: formatValue(route.convoy_size),
    startDate: formatDate(route.start_date),
    endDate: formatDate(route.end_date),
    isClosed: Boolean(route.closed_at),
    status: route.closed_at
      ? t('driverRoutes.table.closed')
      : t('driverRoutes.table.open'),
    plannedTravelDetails: formatValue(route.planned_travel_details)
  }))
);

const routeCount = computed(() => props.routes.length);

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('driverRoutes.table.emptyValue')
    : value;
}

function formatDate(value) {
  if (!value) {
    return t('driverRoutes.table.emptyValue');
  }

  return new Intl.DateTimeFormat(undefined, {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).format(new Date(`${value}T00:00:00`));
}
</script>

<template>
  <q-card class="driver-routes-card driver-routes-table-card" bordered flat>
    <q-card-section
      class="row items-center justify-between q-col-gutter-md q-pa-lg q-pb-md"
    >
      <div class="col">
        <h2 class="text-h6 text-weight-bold q-my-none">
          {{ t('driverRoutes.table.title') }}
        </h2>
      </div>

      <div class="col-auto driver-routes-muted text-caption text-weight-bold">
        {{ t('driverRoutes.routeCount', { count: routeCount }) }}
      </div>
    </q-card-section>

    <q-table
      class="driver-routes-table"
      flat
      hide-bottom
      row-key="id"
      :rows="rows"
      :columns="columns"
      :loading="props.loading"
      :pagination="tablePagination"
    >
      <template #body-cell-origin="scope">
        <q-td :props="scope">
          <div class="text-weight-bold">
            {{ scope.row.origin }}
          </div>
        </q-td>
      </template>

      <template #body-cell-plannedTravelDetails="scope">
        <q-td :props="scope">
          <div class="driver-routes-details">
            {{ scope.row.plannedTravelDetails }}
          </div>
        </q-td>
      </template>

      <template #body-cell-status="scope">
        <q-td :props="scope">
          <q-badge
            class="driver-routes-status"
            :color="scope.row.isClosed ? 'grey-8' : 'positive'"
            outline
          >
            {{ scope.row.status }}
          </q-badge>
        </q-td>
      </template>

      <template #no-data>
        <div
          class="driver-routes-empty-state row items-center justify-center q-gutter-md q-pa-xl"
        >
          <q-icon class="driver-routes-empty-icon" name="route" size="34px" />
          <div class="column">
            <strong>{{ t('driverRoutes.table.emptyTitle') }}</strong>
            <span>{{ t('driverRoutes.table.emptyDescription') }}</span>
          </div>
        </div>
      </template>
    </q-table>
  </q-card>
</template>
