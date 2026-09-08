<script setup>
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useBidStore } from '@/stores/bid.js';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  routeStopId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits({
  'update:modelValue': value => typeof value === 'boolean'
});

const { t } = useI18n();
const bidStore = useBidStore();
const { routeStopBids } = storeToRefs(bidStore);
const tablePagination = { rowsPerPage: 0 };
const isFetching = ref(false);

const dialogOpen = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
});

const columns = computed(() => [
  {
    name: 'contact',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.contact'),
    field: 'contact',
    align: 'left',
    sortable: true
  },
  {
    name: 'email',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.email'),
    field: 'email',
    align: 'left',
    sortable: true
  },
  {
    name: 'phone',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.phone'),
    field: 'phone',
    align: 'left',
    sortable: true
  },
  {
    name: 'country',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.country'),
    field: 'country',
    align: 'left',
    sortable: true
  },
  {
    name: 'city',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.city'),
    field: 'city',
    align: 'left',
    sortable: true
  },
  {
    name: 'address',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.address'),
    field: 'address',
    align: 'left',
    sortable: true
  },
  {
    name: 'postCode',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.postCode'),
    field: 'postCode',
    align: 'left',
    sortable: true
  },
  {
    name: 'originalPrice',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.originalPrice'),
    field: 'originalPriceValue',
    align: 'left',
    sortable: true
  },
  {
    name: 'price',
    label: t('dispatcherRouteEdit.routeStops.bidsDialog.table.price'),
    field: 'priceValue',
    align: 'left',
    sortable: true
  }
]);

const rows = computed(() =>
  routeStopBids.value.map((bid, index) => {
    const restStop = bid.rest_stop ?? {};
    const user = restStop.user ?? {};

    return {
      id: `${bid.route_stop_id}:${bid.rest_stop_id}:${index}`,
      contact: formatContactName(user),
      email: formatValue(user.email),
      phone: formatValue(user.phone_number),
      country: formatValue(user.country),
      city: formatValue(restStop.city),
      address: formatValue(restStop.address),
      postCode: formatValue(restStop.post_code),
      originalPrice: formatPrice(bid.original_price),
      originalPriceValue: sortablePrice(bid.original_price),
      price: formatPrice(bid.price),
      priceValue: sortablePrice(bid.price)
    };
  })
);

const bidCount = computed(() => routeStopBids.value.length);
const routeStopTitle = computed(() =>
  t('dispatcherRouteEdit.routeStops.bidsDialog.title', {
    id: formatValue(props.routeStopId)
  })
);

watch(
  () => [dialogOpen.value, props.routeStopId],
  ([isOpen]) => {
    if (!isOpen) {
      bidStore.clearRouteStopBids();
      return;
    }

    bidStore.clearRouteStopBids();
    void loadRouteStopBids();
  }
);

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('dispatcherRouteEdit.routeStops.bidsDialog.table.emptyValue')
    : value;
}

function formatContactName(user) {
  const fullName = [user.first_name, user.last_name]
    .filter(value => Boolean(String(value ?? '').trim()))
    .join(' ');

  return formatValue(fullName);
}

function formatPrice(value) {
  const numberValue = Number(value);

  if (!Number.isFinite(numberValue)) {
    return t('dispatcherRouteEdit.routeStops.bidsDialog.table.emptyValue');
  }

  return new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numberValue);
}

function sortablePrice(value) {
  const numberValue = Number(value);

  return Number.isFinite(numberValue) ? numberValue : -1;
}

async function loadRouteStopBids() {
  if (!props.routeStopId || isFetching.value) {
    return;
  }

  isFetching.value = true;

  try {
    await bidStore.fetchDispatcherRouteStopBids(props.routeStopId);
  } finally {
    isFetching.value = false;
  }
}
</script>

<template>
  <q-dialog v-model="dialogOpen">
    <q-card class="dispatcher-route-stop-bids-dialog" bordered flat>
      <q-card-section
        class="row items-start justify-between q-col-gutter-md q-pa-lg q-pb-md"
      >
        <div class="col-12 col-sm">
          <p
            class="dispatcher-route-stop-bids-count text-caption text-weight-bold q-mb-xs"
          >
            {{
              t('dispatcherRouteEdit.routeStops.bidsDialog.bidCount', {
                count: bidCount
              })
            }}
          </p>
          <h2 class="text-h6 text-weight-bold q-my-none">
            {{ routeStopTitle }}
          </h2>
        </div>

        <div class="col-12 col-sm-auto row justify-end q-gutter-sm">
          <q-btn
            flat
            round
            color="primary"
            icon="refresh"
            :aria-label="
              t('dispatcherRouteEdit.routeStops.bidsDialog.actions.refresh')
            "
            :disable="isFetching || !props.routeStopId"
            @click="loadRouteStopBids"
          >
            <q-tooltip>
              {{
                t('dispatcherRouteEdit.routeStops.bidsDialog.actions.refresh')
              }}
            </q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="grey-8"
            icon="close"
            :aria-label="
              t('dispatcherRouteEdit.routeStops.bidsDialog.actions.close')
            "
            v-close-popup
          >
            <q-tooltip>
              {{ t('dispatcherRouteEdit.routeStops.bidsDialog.actions.close') }}
            </q-tooltip>
          </q-btn>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-table
          class="dispatcher-route-stop-bids-table"
          flat
          hide-bottom
          row-key="id"
          :rows="rows"
          :columns="columns"
          :loading="isFetching"
          :pagination="tablePagination"
        >
          <template #body-cell-contact="scope">
            <q-td :props="scope">
              <div class="dispatcher-route-stop-bids-contact text-weight-bold">
                {{ scope.row.contact }}
              </div>
            </q-td>
          </template>

          <template #body-cell-address="scope">
            <q-td :props="scope">
              <div class="dispatcher-route-stop-bids-address">
                {{ scope.row.address }}
              </div>
            </q-td>
          </template>

          <template #body-cell-originalPrice="scope">
            <q-td :props="scope">
              {{ scope.row.originalPrice }}
            </q-td>
          </template>

          <template #body-cell-price="scope">
            <q-td :props="scope">
              <strong>{{ scope.row.price }}</strong>
            </q-td>
          </template>

          <template #no-data>
            <div
              class="dispatcher-route-stop-bids-empty-state row items-center justify-center q-gutter-md q-pa-xl"
            >
              <q-icon
                class="dispatcher-route-stop-bids-empty-icon"
                name="local_offer"
                size="34px"
              />
              <div class="column">
                <strong>
                  {{
                    t('dispatcherRouteEdit.routeStops.bidsDialog.emptyTitle')
                  }}
                </strong>
                <span>
                  {{
                    t(
                      'dispatcherRouteEdit.routeStops.bidsDialog.emptyDescription'
                    )
                  }}
                </span>
              </div>
            </div>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>
