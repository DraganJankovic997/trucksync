<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import RouteStopServicesTable from '@/components/bidding/RouteStopServicesTable.vue';

const props = defineProps({
  routeStop: {
    type: Object,
    default: null
  },
  restStopServices: {
    type: Array,
    default: () => []
  },
  bid: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  savingBid: {
    type: Boolean,
    default: false
  },
  bidDisabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits({
  saveBid: payload =>
    payload?.originalPrice !== undefined &&
    payload?.originalPrice !== null &&
    payload?.price !== undefined &&
    payload?.price !== null
});

const { t } = useI18n();
const bidFormRef = ref(null);
const customBidPrice = ref('');

const services = computed(() => props.routeStop?.services ?? []);
const serviceCount = computed(() => services.value.length);
const offeredRestStopServices = computed(() => {
  const serviceMap = new Map();

  props.restStopServices.forEach(service => {
    serviceMap.set(String(service.id), service);
  });

  return serviceMap;
});

const pricedServices = computed(() =>
  services.value.map(service => {
    const serviceKey = String(service.id);
    const offeredService =
      offeredRestStopServices.value.get(serviceKey) ?? null;
    const isOffered = offeredService !== null;
    const pricePerUnit = isOffered
      ? (parsePrice(offeredService.price_per_unit) ?? 0)
      : null;
    const quantity = parseQuantity(service.quantity);
    const lineTotal =
      isOffered && pricePerUnit !== null && quantity !== null
        ? roundPrice(pricePerUnit * quantity)
        : null;

    return {
      ...service,
      bid_service_available: isOffered,
      bid_price_per_unit: pricePerUnit,
      bid_line_total: lineTotal
    };
  })
);
const fullPriceTotal = computed(() =>
  pricedServices.value.reduce(
    (total, service) => total + Number(service.bid_line_total ?? 0),
    0
  )
);
const formattedFullPriceTotal = computed(() =>
  formatPrice(fullPriceTotal.value)
);
const unavailableServices = computed(() =>
  pricedServices.value.filter(service => !service.bid_service_available)
);
const hasUnavailableServices = computed(
  () => unavailableServices.value.length > 0
);
const unavailableServiceNames = computed(() =>
  unavailableServices.value.map(service => formatValue(service.name)).join(', ')
);
const hasBidBlocker = computed(
  () =>
    props.bidDisabled ||
    services.value.length === 0 ||
    hasUnavailableServices.value
);
const isBidFormDisabled = computed(
  () => hasBidBlocker.value || props.loading || props.savingBid
);
const showBidUnavailableMessage = computed(
  () => !props.loading && hasBidBlocker.value
);
const bidUnavailableMessage = computed(() => {
  if (hasUnavailableServices.value) {
    return t('bidding.bidForm.unavailableMissingServices');
  }

  if (props.bidDisabled) {
    return t('bidding.bidForm.unavailableProfile');
  }

  if (services.value.length === 0) {
    return t('bidding.bidForm.unavailableNoServices');
  }

  return t('bidding.bidForm.unavailable');
});
const showBidControls = computed(
  () => props.loading || !showBidUnavailableMessage.value
);
const bidButtonLabel = computed(() =>
  props.bid
    ? t('bidding.bidForm.actions.update')
    : t('bidding.bidForm.actions.submit')
);
const bidPricePrefillKey = computed(() => {
  if (!props.routeStop?.id) {
    return null;
  }

  return `${props.routeStop.id}:${
    props.bid?.price ?? formatPriceInput(fullPriceTotal.value)
  }`;
});
const stopDetails = computed(() => [
  {
    key: 'location',
    label: t('bidding.routeStopServices.fields.location'),
    value: formatValue(props.routeStop?.location)
  },
  {
    key: 'stopAt',
    label: t('bidding.routeStopServices.fields.stopAt'),
    value: formatDateTime(props.routeStop?.stop_at)
  },
  {
    key: 'numberOfTrucks',
    label: t('bidding.routeStopServices.fields.numberOfTrucks'),
    value: formatValue(props.routeStop?.number_of_trucks)
  },
  {
    key: 'numberOfDrivers',
    label: t('bidding.routeStopServices.fields.numberOfDrivers'),
    value: formatValue(props.routeStop?.number_of_drivers)
  }
]);

const bidPriceRequired = value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', {
    field: t('bidding.bidForm.fields.customPrice.label')
  });
const bidPriceDecimal = value =>
  /^\d+(\.\d{1,2})?$/.test(String(value ?? '').trim()) ||
  t('bidding.bidForm.fields.customPrice.decimal');
const bidPriceMin = value =>
  Number(value) >= 0 ||
  t('validation.min', {
    field: t('bidding.bidForm.fields.customPrice.label'),
    min: 0
  });
const bidPriceRules = [bidPriceRequired, bidPriceDecimal, bidPriceMin];

watch(
  bidPricePrefillKey,
  prefillKey => {
    if (!prefillKey) {
      customBidPrice.value = '';
      return;
    }

    customBidPrice.value = formatPriceInput(
      props.bid?.price ?? fullPriceTotal.value
    );
    bidFormRef.value?.resetValidation();
  },
  { immediate: true }
);

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('bidding.emptyValue')
    : value;
}

function formatDateTime(value) {
  if (!value) {
    return t('bidding.emptyValue');
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

function parsePrice(value) {
  const numberValue = Number(value);

  return Number.isFinite(numberValue) ? numberValue : null;
}

function parseQuantity(value) {
  const numberValue = Number(value);

  return Number.isFinite(numberValue) ? numberValue : null;
}

function roundPrice(value) {
  return Math.round((value + Number.EPSILON) * 100) / 100;
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

function formatPriceInput(value) {
  const numberValue = Number(value);

  return Number.isFinite(numberValue) ? numberValue.toFixed(2) : '';
}

async function handleBidSubmit() {
  if (isBidFormDisabled.value) {
    return;
  }

  const isValid = await bidFormRef.value?.validate();

  if (!isValid) {
    return;
  }

  emit('saveBid', {
    originalPrice: formatPriceInput(fullPriceTotal.value),
    price: formatPriceInput(customBidPrice.value)
  });
}
</script>

<template>
  <q-card class="bidding-card bidding-route-stop-services-card" bordered flat>
    <q-card-section
      class="row items-start justify-between q-col-gutter-md q-pa-lg q-pb-md"
    >
      <div class="col-12 col-sm">
        <p class="bidding-eyebrow text-caption text-weight-bold q-mb-xs">
          {{ t('bidding.routeStopServices.eyebrow') }}
        </p>
        <h2 class="text-h5 text-weight-bold q-my-none">
          {{ t('bidding.routeStopServices.title') }}
        </h2>
      </div>

      <div
        class="col-12 col-sm-auto bidding-muted text-caption text-weight-bold"
      >
        {{
          t('bidding.routeStopServices.serviceCount', {
            count: serviceCount
          })
        }}
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pa-lg">
      <div class="bidding-details-grid">
        <div
          v-for="detail in stopDetails"
          :key="detail.key"
          class="bidding-detail"
        >
          <span class="bidding-detail-label">{{ detail.label }}</span>
          <q-skeleton v-if="props.loading && !props.routeStop" type="text" />
          <strong v-else>{{ detail.value }}</strong>
        </div>

        <div class="bidding-detail bidding-detail-wide">
          <span class="bidding-detail-label">
            {{ t('bidding.routeStopServices.fields.description') }}
          </span>
          <q-skeleton v-if="props.loading && !props.routeStop" type="text" />
          <strong v-else>{{
            formatValue(props.routeStop?.description)
          }}</strong>
        </div>
      </div>
    </q-card-section>

    <RouteStopServicesTable
      :services="pricedServices"
      :loading="props.loading"
    />

    <q-separator />

    <q-form
      ref="bidFormRef"
      greedy
      :aria-label="t('bidding.bidForm.ariaLabel')"
      @submit.prevent="handleBidSubmit"
    >
      <q-card-section class="bidding-bid-form q-pa-lg">
        <q-banner
          v-if="hasUnavailableServices"
          dense
          rounded
          class="bidding-bid-warning q-mb-md"
        >
          {{
            t('bidding.bidForm.missingPrices', {
              services: unavailableServiceNames
            })
          }}
        </q-banner>

        <q-banner
          v-if="showBidUnavailableMessage"
          dense
          rounded
          class="bidding-bid-unavailable q-mb-none"
        >
          {{ bidUnavailableMessage }}
        </q-banner>

        <div v-show="showBidControls" class="row q-col-gutter-md items-center">
          <div class="col-12 col-md">
            <div class="bidding-bid-total">
              <span class="bidding-section-label">
                {{ t('bidding.bidForm.fullPriceTotal') }}
              </span>
              <strong>{{ formattedFullPriceTotal }}</strong>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <q-input
              v-model="customBidPrice"
              lazy-rules
              type="number"
              min="0"
              step="0.01"
              name="bid_price"
              :label="t('bidding.bidForm.fields.customPrice.label')"
              :placeholder="t('bidding.bidForm.fields.customPrice.placeholder')"
              :disable="isBidFormDisabled"
              :rules="bidPriceRules"
              hide-bottom-space
              class="full-height"
            >
              <template #prepend>
                <q-icon name="payments" />
              </template>
            </q-input>
          </div>

          <div class="col-12 col-md-auto">
            <q-btn
              class="bidding-bid-submit text-weight-bold"
              type="submit"
              color="primary"
              icon="local_offer"
              no-caps
              unelevated
              :label="bidButtonLabel"
              :loading="props.savingBid"
              :disable="isBidFormDisabled"
            />
          </div>
        </div>
      </q-card-section>
    </q-form>
  </q-card>
</template>
