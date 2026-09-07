<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  route: {
    type: Object,
    default: null
  },
  dispatcher: {
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

const routeDetails = computed(() => [
  {
    key: 'origin',
    label: t('bidding.routeDetails.fields.origin'),
    value: formatValue(props.route?.origin)
  },
  {
    key: 'destination',
    label: t('bidding.routeDetails.fields.destination'),
    value: formatValue(props.route?.destination)
  },
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

const dispatcherDetails = computed(() => [
  {
    key: 'companyName',
    label: t('bidding.routeDetails.dispatcher.companyName'),
    value: formatValue(props.dispatcher?.company_name)
  },
  {
    key: 'city',
    label: t('bidding.routeDetails.dispatcher.city'),
    value: formatValue(props.dispatcher?.city)
  },
  {
    key: 'address',
    label: t('bidding.routeDetails.dispatcher.address'),
    value: formatValue(props.dispatcher?.address)
  },
  {
    key: 'postCode',
    label: t('bidding.routeDetails.dispatcher.postCode'),
    value: formatValue(props.dispatcher?.post_code)
  },
  {
    key: 'registrationNumber',
    label: t('bidding.routeDetails.dispatcher.registrationNumber'),
    value: formatValue(props.dispatcher?.registration_number)
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
            {{
              t('bidding.routeDetails.title', {
                id: formatValue(props.route?.id)
              })
            }}
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

      <div class="bidding-details-grid q-mb-lg">
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

      <div class="bidding-section-label q-mb-sm">
        {{ t('bidding.routeDetails.dispatcherSection') }}
      </div>

      <div class="bidding-details-grid">
        <div
          v-for="detail in dispatcherDetails"
          :key="detail.key"
          class="bidding-detail"
        >
          <span class="bidding-detail-label">{{ detail.label }}</span>
          <q-skeleton v-if="props.loading && !props.dispatcher" type="text" />
          <strong v-else>{{ detail.value }}</strong>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
