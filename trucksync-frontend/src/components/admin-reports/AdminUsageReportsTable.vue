<script setup>
import { computed, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { useRestStopStore } from '@/stores/rest-stop.js';
import { useRouteStopUsageStore } from '@/stores/route-stop-usage.js';

const { t } = useI18n();
const restStopStore = useRestStopStore();
const routeStopUsageStore = useRouteStopUsageStore();
const { restStops } = storeToRefs(restStopStore);
const { routeStopUsages } = storeToRefs(routeStopUsageStore);

const rowsPerPageOptions = [10, 15, 25, 50, 100];
const isFetching = ref(false);
const isFetchingRestStops = ref(false);
const isReport = ref(true);
const selectedRestStopId = ref(null);
const pagination = ref({
  page: 1,
  rowsPerPage: 15,
  rowsNumber: 0
});

const columns = computed(() => [
  {
    name: 'id',
    label: t('adminReports.table.columns.id'),
    field: 'id',
    align: 'left'
  },
  {
    name: 'usedAt',
    label: t('adminReports.table.columns.usedAt'),
    field: 'usedAt',
    align: 'left'
  },
  {
    name: 'restStop',
    label: t('adminReports.table.columns.restStop'),
    field: 'restStop',
    align: 'left'
  },
  {
    name: 'routeStopId',
    label: t('adminReports.table.columns.routeStopId'),
    field: 'routeStopId',
    align: 'left'
  },
  {
    name: 'driverId',
    label: t('adminReports.table.columns.driverId'),
    field: 'driverId',
    align: 'left'
  },
  {
    name: 'rating',
    label: t('adminReports.table.columns.rating'),
    field: 'rating',
    align: 'left'
  },
  {
    name: 'isReport',
    label: t('adminReports.table.columns.isReport'),
    field: 'isReportLabel',
    align: 'left'
  },
  {
    name: 'report',
    label: t('adminReports.table.columns.report'),
    field: 'report',
    align: 'left'
  },
  {
    name: 'createdAt',
    label: t('adminReports.table.columns.createdAt'),
    field: 'createdAt',
    align: 'left'
  },
  {
    name: 'updatedAt',
    label: t('adminReports.table.columns.updatedAt'),
    field: 'updatedAt',
    align: 'left'
  }
]);

const restStopOptions = computed(() => [
  {
    id: null,
    companyName: t('adminReports.filters.allRestStops')
  },
  ...restStops.value.map(restStop => ({
    id: restStop.id,
    companyName: restStopCompanyName(restStop)
  }))
]);

const restStopNameById = computed(
  () =>
    new Map(
      restStops.value.map(restStop => [
        String(restStop.id),
        restStopCompanyName(restStop)
      ])
    )
);

const selectedRestStopLabel = computed(() => {
  if (
    selectedRestStopId.value === null ||
    selectedRestStopId.value === undefined
  ) {
    return t('adminReports.filters.allRestStops');
  }

  return formatRestStop(selectedRestStopId.value);
});

const rows = computed(() =>
  routeStopUsages.value.map(routeStopUsage => ({
    id: routeStopUsage.id,
    routeStopId: routeStopUsage.route_stop_id,
    driverId: routeStopUsage.driver_id,
    restStopId: routeStopUsage.rest_stop_id,
    restStop: formatRestStop(routeStopUsage.rest_stop_id),
    usedAt: formatDateTime(routeStopUsage.used_at),
    rating: formatRating(routeStopUsage.rating),
    isReport: routeStopUsage.is_report === true,
    isReportLabel:
      routeStopUsage.is_report === true
        ? t('adminReports.table.yes')
        : t('adminReports.table.no'),
    report: formatValue(routeStopUsage.report),
    createdAt: formatDateTime(routeStopUsage.created_at),
    updatedAt: formatDateTime(routeStopUsage.updated_at)
  }))
);

const rowCount = computed(() => pagination.value.rowsNumber);

onMounted(() => {
  void loadInitialData();
});

async function loadInitialData() {
  isFetchingRestStops.value = true;

  try {
    await Promise.all([restStopStore.fetchAdminRestStops(), loadReports()]);
  } finally {
    isFetchingRestStops.value = false;
  }
}

async function loadReports(tableState = {}) {
  const requestPagination = tableState.pagination ?? pagination.value;

  isFetching.value = true;

  try {
    const response = await routeStopUsageStore.fetchAdminRatings(
      selectedRestStopId.value,
      requestPagination.page,
      requestPagination.rowsPerPage,
      isReport.value
    );
    const meta = response?.meta ?? {};

    pagination.value = {
      page: meta.current_page ?? requestPagination.page,
      rowsPerPage: meta.per_page ?? requestPagination.rowsPerPage,
      rowsNumber: meta.total ?? 0
    };
  } finally {
    isFetching.value = false;
  }
}

function refreshReports() {
  void loadReports();
}

function handleFilterChange() {
  pagination.value = {
    ...pagination.value,
    page: 1
  };

  void loadReports({
    pagination: pagination.value
  });
}

function restStopCompanyName(restStop) {
  return (
    formatText(restStop.company_name) ||
    formatText(
      [restStop.user?.first_name, restStop.user?.last_name].join(' ')
    ) ||
    formatText(restStop.user?.email) ||
    formatRestStopAddress(restStop) ||
    t('adminReports.table.emptyValue')
  );
}

function formatRestStop(restStopId) {
  if (restStopId === undefined || restStopId === null) {
    return t('adminReports.table.emptyValue');
  }

  return (
    restStopNameById.value.get(String(restStopId)) ??
    t('adminReports.table.restStopFallback', { id: restStopId })
  );
}

function formatRestStopAddress(restStop) {
  return formatText([restStop.address, restStop.city].join(', '));
}

function formatRating(value) {
  const rating = Number(value);

  if (!Number.isFinite(rating)) {
    return t('adminReports.table.emptyValue');
  }

  return t('adminReports.table.ratingValue', { rating: rating });
}

function formatValue(value) {
  return formatText(value) || t('adminReports.table.emptyValue');
}

function formatText(value) {
  const normalizedValue = String(value ?? '').trim();

  return normalizedValue !== '' ? normalizedValue : null;
}

function formatDateTime(value) {
  if (!value) {
    return t('adminReports.table.emptyValue');
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
</script>

<template>
  <q-card class="admin-reports-card admin-reports-table-card" bordered flat>
    <q-table
      v-model:pagination="pagination"
      class="admin-reports-table"
      flat
      row-key="id"
      :rows="rows"
      :columns="columns"
      :loading="isFetching"
      :rows-per-page-options="rowsPerPageOptions"
      @request="loadReports"
    >
      <template #top-left>
        <div>
          <h2 class="text-h6 text-weight-bold q-my-none">
            {{ t('adminReports.table.title') }}
          </h2>
          <div class="admin-reports-muted text-caption text-weight-bold">
            {{ t('adminReports.table.rowCount', { count: rowCount }) }}
          </div>
        </div>
      </template>

      <template #top-right>
        <div class="admin-reports-controls row items-center q-gutter-sm">
          <q-checkbox
            v-model="isReport"
            dense
            :label="t('adminReports.filters.isReport')"
            @update:model-value="handleFilterChange"
          />

          <q-select
            v-model="selectedRestStopId"
            class="admin-reports-rest-stop-select"
            dense
            outlined
            clearable
            emit-value
            map-options
            option-label="companyName"
            option-value="id"
            :display-value="selectedRestStopLabel"
            :label="t('adminReports.filters.restStop')"
            :options="restStopOptions"
            :loading="isFetchingRestStops"
            @update:model-value="handleFilterChange"
          />

          <q-btn
            color="primary"
            icon="refresh"
            outline
            no-caps
            class="text-weight-bold"
            :label="t('adminReports.actions.refresh')"
            :loading="isFetching || isFetchingRestStops"
            @click="refreshReports"
          />
        </div>
      </template>

      <template #body-cell-id="scope">
        <q-td :props="scope">
          <span class="text-weight-bold">{{ scope.row.id }}</span>
        </q-td>
      </template>

      <template #body-cell-restStop="scope">
        <q-td :props="scope">
          <div class="admin-reports-rest-stop text-weight-bold">
            {{ scope.row.restStop }}
          </div>
        </q-td>
      </template>

      <template #body-cell-isReport="scope">
        <q-td :props="scope">
          <q-badge
            class="admin-reports-status"
            :color="scope.row.isReport ? 'negative' : 'grey-7'"
            outline
          >
            {{ scope.row.isReportLabel }}
          </q-badge>
        </q-td>
      </template>

      <template #body-cell-report="scope">
        <q-td :props="scope">
          <div class="admin-reports-report">
            {{ scope.row.report }}
          </div>
        </q-td>
      </template>

      <template #no-data>
        <div
          class="admin-reports-empty-state row items-center justify-center q-gutter-md q-pa-xl"
        >
          <q-icon class="admin-reports-empty-icon" name="report" size="34px" />
          <div class="column">
            <strong>{{ t('adminReports.table.emptyTitle') }}</strong>
            <span>{{ t('adminReports.table.emptyDescription') }}</span>
          </div>
        </div>
      </template>
    </q-table>
  </q-card>
</template>
