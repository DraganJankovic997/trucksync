<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import RouteStopServicesTable from '@/components/bidding/RouteStopServicesTable.vue';

const props = defineProps({
  routeStop: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const { t } = useI18n();

const services = computed(() => props.routeStop?.services ?? []);
const serviceCount = computed(() => services.value.length);
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

    <RouteStopServicesTable :services="services" :loading="props.loading" />
  </q-card>
</template>
