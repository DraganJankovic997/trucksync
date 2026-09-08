<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  services: {
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
    name: 'name',
    label: t('bidding.routeStopServices.table.name'),
    field: 'name',
    align: 'left',
    sortable: true
  },
  {
    name: 'quantity',
    label: t('bidding.routeStopServices.table.quantity'),
    field: 'quantityValue',
    align: 'left',
    sortable: true
  },
  {
    name: 'measurement_unit',
    label: t('bidding.routeStopServices.table.measurementUnit'),
    field: 'measurementUnit',
    align: 'left',
    sortable: true
  },
  {
    name: 'price_per_unit',
    label: t('bidding.routeStopServices.table.pricePerUnit'),
    field: 'pricePerUnitValue',
    align: 'left',
    sortable: true
  },
  {
    name: 'line_total',
    label: t('bidding.routeStopServices.table.lineTotal'),
    field: 'lineTotalValue',
    align: 'left',
    sortable: true
  }
]);

const rows = computed(() =>
  props.services.map(service => ({
    id: service.id,
    name: formatValue(service.name),
    quantity: formatQuantity(service.quantity),
    quantityValue: Number(service.quantity ?? 0),
    measurementUnit: formatValue(service.measurement_unit),
    serviceAvailable: service.bid_service_available !== false,
    pricePerUnit: formatPrice(service.bid_price_per_unit),
    pricePerUnitValue: getSortablePrice(service.bid_price_per_unit),
    lineTotal: formatPrice(service.bid_line_total),
    lineTotalValue: getSortablePrice(service.bid_line_total)
  }))
);

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('bidding.emptyValue')
    : value;
}

function formatQuantity(value) {
  const numberValue = Number(value);

  if (Number.isNaN(numberValue)) {
    return formatValue(value);
  }

  return new Intl.NumberFormat(undefined, {
    maximumFractionDigits: 2
  }).format(numberValue);
}

function formatPrice(value) {
  const numberValue = Number(value);

  if (!Number.isFinite(numberValue)) {
    return t('bidding.routeStopServices.table.unavailable');
  }

  return new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numberValue);
}

function getSortablePrice(value) {
  const numberValue = Number(value);

  return Number.isFinite(numberValue) ? numberValue : -1;
}
</script>

<template>
  <q-table
    class="bidding-route-stop-services-table"
    flat
    hide-bottom
    row-key="id"
    :rows="rows"
    :columns="columns"
    :loading="props.loading"
    :pagination="tablePagination"
  >
    <template #body-cell-name="scope">
      <q-td
        :props="scope"
        :class="{ 'bidding-service-unavailable': !scope.row.serviceAvailable }"
      >
        <div class="bidding-service-name text-weight-bold">
          {{ scope.row.name }}
        </div>
      </q-td>
    </template>

    <template #body-cell-quantity="scope">
      <q-td
        :props="scope"
        :class="{ 'bidding-service-unavailable': !scope.row.serviceAvailable }"
      >
        <q-badge
          class="bidding-service-quantity"
          :class="{
            'bidding-service-unavailable': !scope.row.serviceAvailable
          }"
          outline
        >
          {{ scope.row.quantity }}
        </q-badge>
      </q-td>
    </template>

    <template #body-cell-measurement_unit="scope">
      <q-td
        :props="scope"
        :class="{ 'bidding-service-unavailable': !scope.row.serviceAvailable }"
      >
        {{ scope.row.measurementUnit }}
      </q-td>
    </template>

    <template #body-cell-price_per_unit="scope">
      <q-td
        :props="scope"
        :class="{ 'bidding-service-unavailable': !scope.row.serviceAvailable }"
      >
        {{ scope.row.pricePerUnit }}
      </q-td>
    </template>

    <template #body-cell-line_total="scope">
      <q-td
        :props="scope"
        :class="{ 'bidding-service-unavailable': !scope.row.serviceAvailable }"
      >
        <strong>{{ scope.row.lineTotal }}</strong>
      </q-td>
    </template>

    <template #no-data>
      <div
        class="bidding-empty-state row items-center justify-center q-gutter-md q-pa-xl"
      >
        <q-icon class="bidding-empty-icon" name="fact_check" size="34px" />
        <div class="column">
          <strong>{{ t('bidding.routeStopServices.table.emptyTitle') }}</strong>
          <span>{{
            t('bidding.routeStopServices.table.emptyDescription')
          }}</span>
        </div>
      </div>
    </template>
  </q-table>
</template>
