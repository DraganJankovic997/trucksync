<script setup>
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useBidStore } from '@/stores/bid.js';

const props = defineProps({
  status: {
    type: String,
    default: null
  },
  from: {
    type: String,
    default: null
  },
  title: {
    type: String,
    default: null
  }
});

const { t } = useI18n();
const bidStore = useBidStore();
const rowsPerPageOptions = [10, 15, 25, 50, 100];

const bids = ref([]);
const isFetching = ref(false);
const pagination = ref({
  page: 1,
  rowsPerPage: 15,
  rowsNumber: 0
});

const columns = computed(() => [
  {
    name: 'routeStopId',
    label: t('bidding.bidsTable.columns.routeStopId'),
    field: 'routeStopId',
    align: 'left'
  },
  {
    name: 'stopAt',
    label: t('bidding.bidsTable.columns.stopAt'),
    field: 'stopAt',
    align: 'left'
  },
  {
    name: 'dispatcherCompany',
    label: t('bidding.bidsTable.columns.dispatcherCompany'),
    field: 'dispatcherCompany',
    align: 'left'
  },
  {
    name: 'dispatcherAddress',
    label: t('bidding.bidsTable.columns.dispatcherAddress'),
    field: 'dispatcherAddress',
    align: 'left'
  },
  {
    name: 'contact',
    label: t('bidding.bidsTable.columns.contact'),
    field: 'contact',
    align: 'left'
  },
  {
    name: 'numberOfTrucks',
    label: t('bidding.bidsTable.columns.numberOfTrucks'),
    field: 'numberOfTrucks',
    align: 'left'
  },
  {
    name: 'numberOfDrivers',
    label: t('bidding.bidsTable.columns.numberOfDrivers'),
    field: 'numberOfDrivers',
    align: 'left'
  },
  {
    name: 'status',
    label: t('bidding.bidsTable.columns.status'),
    field: 'statusLabel',
    align: 'left'
  },
  {
    name: 'originalPrice',
    label: t('bidding.bidsTable.columns.originalPrice'),
    field: 'originalPrice',
    align: 'left'
  },
  {
    name: 'price',
    label: t('bidding.bidsTable.columns.price'),
    field: 'price',
    align: 'left'
  }
]);

const rows = computed(() =>
  bids.value.map(bid => {
    const routeStop = bid.route_stop ?? {};
    const dispatcher = routeStop.dispatcher ?? {};
    const dispatcherUser = dispatcher.user ?? {};

    return {
      id: `${bid.route_stop_id}-${bid.rest_stop_id}`,
      routeStopId: bid.route_stop_id,
      stopAt: formatDateTime(routeStop.stop_at),
      dispatcherCompany: formatValue(dispatcher.company_name),
      dispatcherAddress: formatAddress(dispatcher),
      contact: formatContactName(dispatcherUser),
      email: formatValue(dispatcherUser.email),
      phone: formatValue(dispatcherUser.phone_number),
      numberOfTrucks: formatValue(routeStop.number_of_trucks),
      numberOfDrivers: formatValue(routeStop.number_of_drivers),
      status: bid.status,
      statusLabel: formatStatus(bid.status),
      statusColor: statusColor(bid.status),
      originalPrice: formatPrice(bid.original_price),
      price: formatPrice(bid.price)
    };
  })
);

const bidCount = computed(() => pagination.value.rowsNumber);
const tableTitle = computed(() => props.title ?? t('bidding.bidsTable.title'));

onMounted(() => {
  void loadBids();
});

async function loadBids(tableState = {}) {
  const requestPagination = tableState.pagination ?? pagination.value;

  isFetching.value = true;

  try {
    const response = await bidStore.fetchRestStopBids(
      props.status,
      requestPagination.page,
      requestPagination.rowsPerPage,
      props.from
    );
    const meta = response?.meta ?? {};

    bids.value = response?.data?.bids ?? [];
    pagination.value = {
      page: meta.current_page ?? requestPagination.page,
      rowsPerPage: meta.per_page ?? requestPagination.rowsPerPage,
      rowsNumber: meta.total ?? 0
    };
  } finally {
    isFetching.value = false;
  }
}

function refreshBids() {
  void loadBids();
}

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('bidding.emptyValue')
    : value;
}

function formatContactName(user) {
  const fullName = [user.first_name, user.last_name]
    .filter(value => Boolean(String(value ?? '').trim()))
    .join(' ');

  return formatValue(fullName);
}

function formatAddress(dispatcher) {
  const address = [dispatcher.address, dispatcher.city, dispatcher.post_code]
    .filter(value => Boolean(String(value ?? '').trim()))
    .join(', ');

  return formatValue(address);
}

function formatStatus(status) {
  if (!status) {
    return t('bidding.emptyValue');
  }

  return t(`bidding.bidsTable.status.${status}`);
}

function statusColor(status) {
  if (status === 'selected') {
    return 'positive';
  }

  if (status === 'rejected') {
    return 'negative';
  }

  return 'warning';
}

function formatPrice(value) {
  const numberValue = Number(value);

  if (!Number.isFinite(numberValue)) {
    return t('bidding.emptyValue');
  }

  return new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numberValue);
}

function formatDateTime(value) {
  if (!value) {
    return t('bidding.emptyValue');
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
  <q-card class="bids-table-card" bordered flat>
    <q-table
      v-model:pagination="pagination"
      class="bids-table"
      flat
      row-key="id"
      :rows="rows"
      :columns="columns"
      :loading="isFetching"
      :rows-per-page-options="rowsPerPageOptions"
      @request="loadBids"
    >
      <template #top-left>
        <div>
          <h2 class="text-h6 text-weight-bold q-my-none">
            {{ tableTitle }}
          </h2>
          <div class="bids-table-muted text-caption text-weight-bold">
            {{ t('bidding.bidsTable.bidCount', { count: bidCount }) }}
          </div>
        </div>
      </template>

      <template #top-right>
        <q-btn
          color="primary"
          icon="refresh"
          outline
          no-caps
          class="text-weight-bold"
          :label="t('bidding.bidsTable.actions.refresh')"
          :loading="isFetching"
          @click="refreshBids"
        />
      </template>

      <template #body-cell-routeStopId="scope">
        <q-td :props="scope">
          <div class="bids-table-route-stop text-weight-bold">
            {{ scope.row.routeStopId }}
          </div>
        </q-td>
      </template>

      <template #body-cell-dispatcherCompany="scope">
        <q-td :props="scope">
          <div class="bids-table-text text-weight-bold">
            {{ scope.row.dispatcherCompany }}
          </div>
        </q-td>
      </template>

      <template #body-cell-dispatcherAddress="scope">
        <q-td :props="scope">
          <div class="bids-table-text">
            {{ scope.row.dispatcherAddress }}
          </div>
        </q-td>
      </template>

      <template #body-cell-contact="scope">
        <q-td :props="scope">
          <div class="bids-table-text column q-gutter-xs">
            <div class="text-weight-bold">
              {{ scope.row.contact }}
            </div>
            <div class="bids-table-muted text-caption">
              {{ scope.row.email }}
            </div>
            <div class="bids-table-muted text-caption">
              {{ scope.row.phone }}
            </div>
          </div>
        </q-td>
      </template>

      <template #body-cell-status="scope">
        <q-td :props="scope">
          <q-badge
            class="bids-table-status"
            :color="scope.row.statusColor"
            outline
          >
            {{ scope.row.statusLabel }}
          </q-badge>
        </q-td>
      </template>

      <template #body-cell-price="scope">
        <q-td :props="scope">
          <span class="text-weight-bold">{{ scope.row.price }}</span>
        </q-td>
      </template>

      <template #no-data>
        <div
          class="bids-table-empty-state row items-center justify-center q-gutter-md q-pa-xl"
        >
          <q-icon
            class="bids-table-empty-icon"
            name="local_offer"
            size="34px"
          />
          <div class="column">
            <strong>{{ t('bidding.bidsTable.emptyTitle') }}</strong>
            <span>{{ t('bidding.bidsTable.emptyDescription') }}</span>
          </div>
        </div>
      </template>
    </q-table>
  </q-card>
</template>
