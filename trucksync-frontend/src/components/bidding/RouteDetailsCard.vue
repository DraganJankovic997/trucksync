<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  route: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const { t } = useI18n();

const routeStatus = computed(() =>
  props.route?.closed_at
    ? t('bidding.routeDetails.status.closed')
    : t('bidding.routeDetails.status.open')
);

const routeTitle = computed(() =>
  t('bidding.routeDetails.title', {
    origin: formatValue(props.route?.origin),
    destination: formatValue(props.route?.destination)
  })
);

const routeDetails = computed(() => [
  {
    key: 'convoySize',
    label: t('bidding.routeDetails.fields.convoySize'),
    value: formatValue(props.route?.convoy_size)
  },
  {
    key: 'startDate',
    label: t('bidding.routeDetails.fields.startDate'),
    value: formatDate(props.route?.start_date)
  },
  {
    key: 'endDate',
    label: t('bidding.routeDetails.fields.endDate'),
    value: formatDate(props.route?.end_date)
  }
]);

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('bidding.emptyValue')
    : value;
}

function formatDate(value) {
  if (!value) {
    return t('bidding.emptyValue');
  }

  const dateValue = new Date(`${value}T00:00:00`);

  if (Number.isNaN(dateValue.getTime())) {
    return formatValue(value);
  }

  return new Intl.DateTimeFormat(undefined, {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).format(dateValue);
}
</script>

<template>
  <q-card class="bidding-card bidding-route-details-card" bordered flat>
    <q-card-section
      class="row items-start justify-between q-col-gutter-md q-pa-lg q-pb-md"
    >
      <div class="col-12 col-md">
        <template v-if="props.loading && !props.route">
          <q-skeleton type="text" width="120px" />
          <q-skeleton type="text" width="260px" height="34px" />
        </template>
        <template v-else>
          <p class="bidding-eyebrow text-caption text-weight-bold q-mb-xs">
            {{ t('bidding.routeDetails.eyebrow') }}
          </p>
          <h2 class="text-h5 text-weight-bold q-my-none">
            {{ routeTitle }}
          </h2>
        </template>
      </div>

      <div class="col-12 col-md-auto">
        <q-skeleton
          v-if="props.loading && !props.route"
          type="text"
          width="80px"
        />
        <q-badge
          v-else
          class="bidding-status"
          :color="props.route?.closed_at ? 'grey-8' : 'positive'"
          outline
        >
          {{ routeStatus }}
        </q-badge>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pa-lg">
      <div class="bidding-section-label q-mb-sm">
        {{ t('bidding.routeDetails.routeSection') }}
      </div>

      <div class="bidding-details-grid bidding-route-details-grid">
        <div
          v-for="detail in routeDetails"
          :key="detail.key"
          class="bidding-detail"
        >
          <span class="bidding-detail-label">{{ detail.label }}</span>
          <q-skeleton v-if="props.loading && !props.route" type="text" />
          <strong v-else>{{ detail.value }}</strong>
        </div>

        <div class="bidding-detail bidding-detail-wide">
          <span class="bidding-detail-label">
            {{ t('bidding.routeDetails.fields.plannedTravelDetails') }}
          </span>
          <q-skeleton v-if="props.loading && !props.route" type="text" />
          <strong v-else>
            {{ formatValue(props.route?.planned_travel_details) }}
          </strong>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
