<script setup>
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useBidStore } from '@/stores/bid.js';
import { useRouteStore } from '@/stores/route.js';
import { useRouteStopStore } from '@/stores/route-stop.js';

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
const routeStore = useRouteStore();
const routeStopStore = useRouteStopStore();
const { routeStopBids } = storeToRefs(bidStore);
const tablePagination = { rowsPerPage: 0 };
const isFetching = ref(false);
const isSubmitting = ref(false);
const selectedBidRowId = ref(null);

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
  routeStopBids.value.map(bid => {
    const restStop = bid.rest_stop ?? {};
    const user = restStop.user ?? {};

    return {
      id: bid.rest_stop_id,
      bid: bid,
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
const selectedBidRow = computed(
  () => rows.value.find(row => row.id === selectedBidRowId.value) ?? null
);
const selectedRestStopId = computed(() => selectedBidRow.value?.id ?? null);

watch(
  () => [dialogOpen.value, props.routeStopId],
  ([isOpen]) => {
    if (!isOpen) {
      selectedBidRowId.value = null;
      bidStore.clearRouteStopBids();
      return;
    }

    selectedBidRowId.value = null;
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

function isRowSelected(row) {
  return selectedBidRowId.value === row.id;
}

function selectBidRow(row) {
  selectedBidRowId.value = row.id;
}

function closeDialog() {
  dialogOpen.value = false;
}

async function submitSelectedBid() {
  if (!props.routeStopId || !selectedRestStopId.value || isSubmitting.value) {
    return;
  }

  isSubmitting.value = true;

  try {
    const fulfilledRouteStop = await routeStopStore.fulfillRouteStop(
      props.routeStopId,
      selectedRestStopId.value
    );

    if (fulfilledRouteStop) {
      await routeStore.fetchRoute(fulfilledRouteStop.route_id);
      dialogOpen.value = false;
    }
  } finally {
    isSubmitting.value = false;
  }
}

async function loadRouteStopBids() {
  if (!props.routeStopId || isFetching.value) {
    return;
  }

  selectedBidRowId.value = null;
  isFetching.value = true;

  try {
    await bidStore.fetchDispatcherRouteStopBids(props.routeStopId);
  } finally {
    isFetching.value = false;
  }
}
</script>

<template>
  <q-dialog v-model="dialogOpen" full-width :persistent="isSubmitting">
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
            :disable="isFetching || isSubmitting || !props.routeStopId"
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
            :disable="isSubmitting"
            v-close-popup
          >
            <q-tooltip>
              {{ t('dispatcherRouteEdit.routeStops.bidsDialog.actions.close') }}
            </q-tooltip>
          </q-btn>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section
        class="dispatcher-route-stop-bids-table-section q-pa-none"
      >
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
          <template #body="scope">
            <q-tr
              :props="scope"
              :aria-selected="isRowSelected(scope.row)"
              :class="{
                'dispatcher-route-stop-bids-row-selected': isRowSelected(
                  scope.row
                )
              }"
              @click="selectBidRow(scope.row)"
            >
              <q-td key="contact" :props="scope">
                <div
                  class="dispatcher-route-stop-bids-contact text-weight-bold"
                >
                  {{ scope.row.contact }}
                </div>
              </q-td>
              <q-td key="email" :props="scope">
                {{ scope.row.email }}
              </q-td>
              <q-td key="phone" :props="scope">
                {{ scope.row.phone }}
              </q-td>
              <q-td key="country" :props="scope">
                {{ scope.row.country }}
              </q-td>
              <q-td key="city" :props="scope">
                {{ scope.row.city }}
              </q-td>
              <q-td key="address" :props="scope">
                <div class="dispatcher-route-stop-bids-address">
                  {{ scope.row.address }}
                </div>
              </q-td>
              <q-td key="postCode" :props="scope">
                {{ scope.row.postCode }}
              </q-td>
              <q-td key="originalPrice" :props="scope">
                {{ scope.row.originalPrice }}
              </q-td>
              <q-td key="price" :props="scope">
                <strong>{{ scope.row.price }}</strong>
              </q-td>
            </q-tr>
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

      <q-separator />

      <q-card-actions
        class="dispatcher-route-stop-bids-actions q-pa-lg"
        align="right"
      >
        <q-btn
          flat
          no-caps
          color="grey-8"
          :label="t('dispatcherRouteEdit.routeStops.bidsDialog.actions.close')"
          :disable="isSubmitting"
          @click="closeDialog"
        />
        <q-btn
          color="primary"
          no-caps
          unelevated
          :disable="!selectedRestStopId"
          :loading="isSubmitting"
          :label="t('dispatcherRouteEdit.routeStops.bidsDialog.actions.submit')"
          @click="submitSelectedBid"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
