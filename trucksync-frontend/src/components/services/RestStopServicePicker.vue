<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: null
  },
  pricePerUnit: {
    type: [Number, String],
    default: ''
  },
  services: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  optionsLoading: {
    type: Boolean,
    default: false
  },
  disable: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits({
  'update:modelValue': value =>
    ['number', 'string'].includes(typeof value) || value === null,
  'update:pricePerUnit': value =>
    ['number', 'string'].includes(typeof value) || value === null,
  add: payload =>
    payload?.serviceId !== undefined &&
    payload?.serviceId !== null &&
    payload?.pricePerUnit !== undefined &&
    payload?.pricePerUnit !== null
});

const { t } = useI18n();
const formRef = ref(null);

const selectedServiceId = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const selectedPricePerUnit = computed({
  get() {
    return props.pricePerUnit;
  },
  set(value) {
    emit('update:pricePerUnit', value);
  }
});

const serviceOptions = computed(() =>
  props.services.map(service => ({
    label: formatServiceOptionLabel(service),
    value: service.id
  }))
);

const selectedService = computed(
  () =>
    props.services.find(
      service => String(service.id) === String(selectedServiceId.value)
    ) ?? null
);

const pricePerUnitSuffix = computed(() =>
  selectedService.value?.measurement_unit
    ? `/ ${selectedService.value.measurement_unit}`
    : ''
);

function formatServiceOptionLabel(service) {
  const serviceName = service.name ?? '';
  const measurementUnit = service.measurement_unit ?? '';

  return measurementUnit ? `${serviceName} (${measurementUnit})` : serviceName;
}

const required = value =>
  (value !== null && value !== undefined && value !== '') ||
  t('validation.required', {
    field: t('restStopServices.form.service.label')
  });

const min = value =>
  Number(value) >= 0 ||
  t('validation.min', {
    field: t('restStopServices.form.pricePerUnit.label'),
    min: 0
  });

const decimal = value =>
  /^\d+(\.\d{1,2})?$/.test(String(value ?? '').trim()) ||
  t('restStopServices.form.pricePerUnit.decimal');

const serviceRules = [required];
const pricePerUnitRules = [
  value =>
    (value !== null && value !== undefined && String(value).trim() !== '') ||
    t('validation.required', {
      field: t('restStopServices.form.pricePerUnit.label')
    }),
  decimal,
  min
];

const isDisabled = computed(
  () =>
    props.disable ||
    props.loading ||
    props.optionsLoading ||
    serviceOptions.value.length === 0
);

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (
    !isValid ||
    selectedServiceId.value === null ||
    selectedServiceId.value === undefined
  ) {
    return;
  }

  emit('add', {
    serviceId: selectedServiceId.value,
    pricePerUnit: String(selectedPricePerUnit.value).trim()
  });
}
</script>

<template>
  <q-form
    ref="formRef"
    greedy
    :aria-label="t('restStopServices.form.ariaLabel')"
    @submit.prevent="handleSubmit"
  >
    <q-card class="service-card" bordered flat>
      <q-card-section class="q-pa-lg q-pb-none">
        <h2 class="text-h6 text-weight-bold q-my-none">
          {{ t('restStopServices.form.title') }}
        </h2>
      </q-card-section>

      <q-card-section class="q-px-lg q-py-md">
        <q-select
          v-model="selectedServiceId"
          outlined
          emit-value
          map-options
          clearable
          option-label="label"
          option-value="value"
          :options="serviceOptions"
          :label="t('restStopServices.form.service.label')"
          :placeholder="t('restStopServices.form.service.placeholder')"
          :loading="props.optionsLoading"
          :disable="props.disable || props.loading || props.optionsLoading"
          :rules="serviceRules"
        />

        <q-input
          v-model="selectedPricePerUnit"
          outlined
          lazy-rules
          type="number"
          min="0"
          step="0.01"
          name="price_per_unit"
          :label="t('restStopServices.form.pricePerUnit.label')"
          :placeholder="t('restStopServices.form.pricePerUnit.placeholder')"
          :suffix="pricePerUnitSuffix"
          :disable="props.disable || props.loading || props.optionsLoading"
          :rules="pricePerUnitRules"
        >
          <template #prepend>
            <q-icon name="payments" />
          </template>
        </q-input>

        <q-banner
          v-if="!props.optionsLoading && serviceOptions.length === 0"
          dense
          rounded
          class="bg-grey-2 text-grey-8 q-mt-sm"
        >
          {{ t('restStopServices.form.noOptions') }}
        </q-banner>
      </q-card-section>

      <q-card-actions class="q-px-lg q-pb-lg q-pt-none">
        <q-btn
          class="full-width text-weight-bold"
          type="submit"
          color="primary"
          icon="add"
          no-caps
          unelevated
          :label="t('restStopServices.form.submit')"
          :loading="props.loading"
          :disable="isDisabled"
        />
      </q-card-actions>
    </q-card>
  </q-form>
</template>
