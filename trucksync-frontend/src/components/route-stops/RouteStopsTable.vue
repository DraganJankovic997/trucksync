<script setup>
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouteStopStore } from '@/stores/route-stop.js';

const { t } = useI18n();
const routeStopStore = useRouteStopStore();
const rowsPerPageOptions = [10, 15, 25, 50, 100];

const isFetching = ref(false);
const routeStops = ref([]);
const search = ref('');
const pagination = ref({
  sortBy: 'stop_at',
  descending: true,
  page: 1,
  rowsPerPage: 15,
  rowsNumber: 0
});

onMounted(() => {
  void loadRouteStops();
});

const columns = computed(() => [
  {
    name: 'id',
    label: t('routeStops.table.id'),
    field: 'id',
    align: 'left',
    sortable: true
  },
  {
    name: 'route_id',
    label: t('routeStops.table.routeId'),
    field: 'routeId',
    align: 'left',
    sortable: true
  },
  {
    name: 'dispatcher_company_name',
    label: t('routeStops.table.dispatcherCompanyName'),
    field: 'dispatcherCompanyName',
    align: 'left',
    sortable: false
  },
  {
    name: 'location',
    label: t('routeStops.table.location'),
    field: 'location',
    align: 'left',
    sortable: true
  },
  {
    name: 'stop_at',
    label: t('routeStops.table.stopAt'),
    field: 'stopAt',
    align: 'left',
    sortable: true
  },
  {
    name: 'description',
    label: t('routeStops.table.description'),
    field: 'description',
    align: 'left',
    sortable: false
  },
  {
    name: 'number_of_trucks',
    label: t('routeStops.table.numberOfTrucks'),
    field: 'numberOfTrucks',
    align: 'left',
    sortable: true
  },
  {
    name: 'number_of_drivers',
    label: t('routeStops.table.numberOfDrivers'),
    field: 'numberOfDrivers',
    align: 'left',
    sortable: true
  },
  {
    name: 'services',
    label: t('routeStops.table.services'),
    field: 'services',
    align: 'left'
  }
]);

const rows = computed(() =>
  routeStops.value.map(routeStop => ({
    id: routeStop.id,
    routeId: routeStop.route_id,
    dispatcherCompanyName: routeStop.dispatcher_company_name,
    location: routeStop.location,
    stopAt: formatDateTime(routeStop.stop_at),
    description: routeStop.description,
    numberOfTrucks: routeStop.number_of_trucks,
    numberOfDrivers: routeStop.number_of_drivers,
    services: formatServices(routeStop.services)
  }))
);

async function loadRouteStops(tableState = {}) {
  const requestPagination = tableState.pagination ?? pagination.value;
  const requestSearch = tableState.filter ?? search.value;
  const sortBy = requestPagination.sortBy || 'stop_at';
  const sortOrder = requestPagination.descending ? 'desc' : 'asc';

  isFetching.value = true;

  try {
    const response = await routeStopStore.fetchUnfulfilledRouteStops(
      normalizedSearch(requestSearch),
      requestPagination.page,
      requestPagination.rowsPerPage,
      sortBy,
      sortOrder
    );
    const meta = response?.meta ?? {};

    routeStops.value = response?.data?.route_stops ?? [];
    pagination.value = {
      sortBy: sortBy,
      descending: requestPagination.descending,
      page: meta.current_page ?? requestPagination.page,
      rowsPerPage: meta.per_page ?? requestPagination.rowsPerPage,
      rowsNumber: meta.total ?? 0
    };
  } finally {
    isFetching.value = false;
  }
}

function normalizedSearch(value) {
  return typeof value === 'string' && value.trim() !== '' ? value.trim() : null;
}

function refreshRouteStops() {
  void loadRouteStops();
}

function formatDateTime(value) {
  if (!value) {
    return value;
  }

  const dateValue = new Date(value);

  if (Number.isNaN(dateValue.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(dateValue);
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

function formatServiceLabel(service) {
  const unit = service.measurement_unit ? ` ${service.measurement_unit}` : '';

  return `${service.name}: ${service.quantity}${unit}`;
}
</script>

<template>
  <q-card class="route-stops-card route-stops-table-card" bordered flat>
    <q-table
      v-model:pagination="pagination"
      class="route-stops-table"
      flat
      binary-state-sort
      row-key="id"
      :rows="rows"
      :columns="columns"
      :filter="search"
      :loading="isFetching"
      :rows-per-page-options="rowsPerPageOptions"
      @request="loadRouteStops"
    >
      <template #top-left>
        <div>
          <h2 class="text-h6 text-weight-bold q-my-none">
            {{ t('routeStops.table.title') }}
          </h2>
          <div class="route-stops-muted text-caption text-weight-bold">
            {{
              t('routeStops.table.routeStopCount', {
                count: pagination.rowsNumber
              })
            }}
          </div>
        </div>
      </template>

      <template #top-right>
        <div class="route-stops-table-actions row items-center q-gutter-sm">
          <q-btn
            color="primary"
            icon="refresh"
            outline
            no-caps
            class="text-weight-bold"
            :label="t('routeStops.actions.refresh')"
            :loading="isFetching"
            @click="refreshRouteStops"
          />

          <q-input
            v-model="search"
            class="route-stops-search"
            dense
            outlined
            debounce="350"
            clearable
            :placeholder="t('routeStops.search.placeholder')"
            :aria-label="t('routeStops.search.label')"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </template>

      <template #body-cell-id="scope">
        <q-td :props="scope">
          <div class="text-weight-bold">
            {{ scope.row.id }}
          </div>
        </q-td>
      </template>

      <template #body-cell-location="scope">
        <q-td :props="scope">
          <div class="route-stops-location text-weight-bold">
            {{ scope.row.location }}
          </div>
        </q-td>
      </template>

      <template #body-cell-description="scope">
        <q-td :props="scope">
          <div class="route-stops-description-cell">
            {{ scope.row.description }}
          </div>
        </q-td>
      </template>

      <template #body-cell-services="scope">
        <q-td :props="scope">
          <div
            v-if="scope.row.services.length > 0"
            class="route-stops-services"
          >
            <q-badge
              v-for="service in scope.row.services"
              :key="`${scope.row.id}-${service.id}`"
              class="route-stops-service"
              outline
            >
              {{ service.label }}
            </q-badge>
          </div>
          <span v-else>
            {{ t('routeStops.table.emptyValue') }}
          </span>
        </q-td>
      </template>

      <template #no-data>
        <div
          class="route-stops-empty-state row items-center justify-center q-gutter-md q-pa-xl"
        >
          <q-icon class="route-stops-empty-icon" name="pin_drop" size="34px" />
          <div class="column">
            <strong>{{ t('routeStops.table.emptyTitle') }}</strong>
            <span>{{ t('routeStops.table.emptyDescription') }}</span>
          </div>
        </div>
      </template>
    </q-table>
  </q-card>
</template>
