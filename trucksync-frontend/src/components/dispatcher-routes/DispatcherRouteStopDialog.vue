<script setup>
import { storeToRefs } from 'pinia';
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import TextField from '@/components/form/TextField.vue';
import { useRouteStore } from '@/stores/route.js';
import { useRouteStopStore } from '@/stores/route-stop.js';
import { useServiceStore } from '@/stores/service.js';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  routeId: {
    type: [Number, String],
    required: true
  },
  routeStop: {
    type: Object,
    default: null
  }
});

const emit = defineEmits({
  'update:modelValue': value => typeof value === 'boolean'
});

const { t } = useI18n();
const routeStore = useRouteStore();
const routeStopStore = useRouteStopStore();
const serviceStore = useServiceStore();
const { services: catalogServices } = storeToRefs(serviceStore);
const formRef = ref(null);
const isFetchingServices = ref(false);
const isSavingRouteStop = ref(false);
let serviceRowId = 0;

const form = reactive({
  location: '',
  description: '',
  numberOfTrucks: '',
  numberOfDrivers: '',
  services: [makeServiceRow()]
});

const dialogOpen = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const isEditMode = computed(() => Boolean(props.routeStop));
const topFieldsDisabled = computed(
  () => isSavingRouteStop.value || isEditMode.value
);
const serviceFieldsDisabled = computed(
  () => isSavingRouteStop.value || isFetchingServices.value
);
const dialogTitle = computed(() =>
  isEditMode.value
    ? t('dispatcherRouteEdit.routeStops.form.editTitle')
    : t('dispatcherRouteEdit.routeStops.form.createTitle')
);
const ariaLabel = computed(() =>
  isEditMode.value
    ? t('dispatcherRouteEdit.routeStops.form.editAriaLabel')
    : t('dispatcherRouteEdit.routeStops.form.createAriaLabel')
);

const locationLabel = computed(() =>
  t('dispatcherRouteEdit.routeStops.form.fields.location.label')
);
const descriptionLabel = computed(() =>
  t('dispatcherRouteEdit.routeStops.form.fields.description.label')
);
const numberOfTrucksLabel = computed(() =>
  t('dispatcherRouteEdit.routeStops.form.fields.numberOfTrucks.label')
);
const numberOfDriversLabel = computed(() =>
  t('dispatcherRouteEdit.routeStops.form.fields.numberOfDrivers.label')
);
const serviceLabel = computed(() =>
  t('dispatcherRouteEdit.routeStops.form.fields.service.label')
);
const quantityLabel = computed(() =>
  t('dispatcherRouteEdit.routeStops.form.fields.quantity.label')
);

const serviceRecords = computed(() => {
  const records = new Map();

  for (const service of [...catalogServices.value, ...routeStopServices()]) {
    if (hasValue(service?.id)) {
      records.set(String(service.id), service);
    }
  }

  return records;
});

const serviceOptions = computed(() =>
  [...serviceRecords.value.values()].map(service => ({
    label: formatServiceOptionLabel(service),
    value: service.id
  }))
);

const hasServiceOptions = computed(() => serviceOptions.value.length > 0);
const canAddServiceRow = computed(
  () =>
    !isSavingRouteStop.value &&
    !isFetchingServices.value &&
    form.services.length < serviceOptions.value.length
);

const required = fieldLabel => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: fieldLabel });

const requiredSelect = fieldLabel => value =>
  hasValue(value) || t('validation.required', { field: fieldLabel });

const integer = fieldLabel => value => {
  const numberValue = Number(value);

  return (
    Number.isInteger(numberValue) ||
    t('validation.integer', { field: fieldLabel })
  );
};

const min = (fieldLabel, minimum) => value =>
  Number(value) >= minimum ||
  t('validation.min', { field: fieldLabel, min: minimum });

const maxLength = (fieldLabel, length) => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', {
    field: fieldLabel,
    length: length
  });

const locationRules = computed(() => [
  required(locationLabel.value),
  maxLength(locationLabel.value, 255)
]);
const activeLocationRules = computed(() =>
  isEditMode.value ? [] : locationRules.value
);
const numberOfTrucksRules = computed(() => [
  required(numberOfTrucksLabel.value),
  integer(numberOfTrucksLabel.value),
  min(numberOfTrucksLabel.value, 1)
]);
const activeNumberOfTrucksRules = computed(() =>
  isEditMode.value ? [] : numberOfTrucksRules.value
);
const numberOfDriversRules = computed(() => [
  required(numberOfDriversLabel.value),
  integer(numberOfDriversLabel.value),
  min(numberOfDriversLabel.value, 1)
]);
const activeNumberOfDriversRules = computed(() =>
  isEditMode.value ? [] : numberOfDriversRules.value
);
const quantityRules = computed(() => [
  required(quantityLabel.value),
  integer(quantityLabel.value),
  min(quantityLabel.value, 1)
]);

watch(dialogOpen, isOpen => {
  if (isOpen) {
    hydrateForm();
    void loadServices();
    return;
  }

  resetForm();
});

watch(
  () => props.routeStop,
  () => {
    if (dialogOpen.value) {
      hydrateForm();
    }
  }
);

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  isSavingRouteStop.value = true;

  try {
    if (isEditMode.value) {
      await routeStopStore.syncRouteStopServices(
        props.routeStop.id,
        servicesPayload()
      );
    } else {
      await routeStopStore.createRouteStop(
        props.routeId,
        form.location.trim(),
        form.description.trim() || null,
        Number(form.numberOfTrucks),
        Number(form.numberOfDrivers),
        servicesPayload()
      );
    }

    await routeStore.fetchRoute(props.routeId);
    dialogOpen.value = false;
  } finally {
    isSavingRouteStop.value = false;
  }
}

async function loadServices() {
  if (isFetchingServices.value) {
    return;
  }

  isFetchingServices.value = true;

  try {
    await serviceStore.fetchServices();
  } finally {
    isFetchingServices.value = false;
  }
}

function hydrateForm() {
  form.location = props.routeStop?.location ?? '';
  form.description = props.routeStop?.description ?? '';
  form.numberOfTrucks = props.routeStop?.number_of_trucks ?? '';
  form.numberOfDrivers = props.routeStop?.number_of_drivers ?? '';
  form.services = serviceRowsFromRouteStop();
  formRef.value?.resetValidation();
}

function resetForm() {
  form.location = '';
  form.description = '';
  form.numberOfTrucks = '';
  form.numberOfDrivers = '';
  form.services = [makeServiceRow()];
  formRef.value?.resetValidation();
}

function routeStopServices() {
  return Array.isArray(props.routeStop?.services)
    ? props.routeStop.services
    : [];
}

function serviceRowsFromRouteStop() {
  const rows = routeStopServices().map(service =>
    makeServiceRow({
      serviceId: service.id,
      quantity: service.quantity
    })
  );

  return rows.length > 0 ? rows : [makeServiceRow()];
}

function makeServiceRow({ serviceId = null, quantity = '' } = {}) {
  serviceRowId += 1;

  return {
    uid: serviceRowId,
    serviceId: serviceId,
    quantity: quantity
  };
}

function addServiceRow() {
  if (!canAddServiceRow.value) {
    return;
  }

  form.services.push(makeServiceRow());
  formRef.value?.resetValidation();
}

function removeServiceRow(uid) {
  const remainingRows = form.services.filter(
    serviceRow => serviceRow.uid !== uid
  );

  form.services = remainingRows.length > 0 ? remainingRows : [makeServiceRow()];
  formRef.value?.resetValidation();
}

function serviceRules(serviceRow) {
  return [
    requiredSelect(serviceLabel.value),
    value =>
      !hasValue(value) ||
      isUniqueServiceSelection(serviceRow, value) ||
      t('dispatcherRouteEdit.routeStops.form.fields.service.duplicate')
  ];
}

function serviceOptionsFor(serviceRow) {
  const selectedServiceIds = new Set(
    form.services
      .filter(row => row.uid !== serviceRow.uid && hasValue(row.serviceId))
      .map(row => String(row.serviceId))
  );

  return serviceOptions.value.filter(
    service =>
      String(service.value) === String(serviceRow.serviceId) ||
      !selectedServiceIds.has(String(service.value))
  );
}

function isUniqueServiceSelection(serviceRow, value) {
  return form.services.every(
    row => row.uid === serviceRow.uid || String(row.serviceId) !== String(value)
  );
}

function selectedMeasurementUnit(serviceRow) {
  return (
    serviceRecords.value.get(String(serviceRow.serviceId))?.measurement_unit ??
    t('dispatcherRouteEdit.routeStops.form.fields.quantity.defaultUnit')
  );
}

function handleServiceChange(serviceRow, serviceId) {
  if (!hasValue(serviceId)) {
    serviceRow.quantity = '';
  }
}

function servicesPayload() {
  return form.services.map(serviceRow => ({
    service_id: serviceRow.serviceId,
    quantity: Number(serviceRow.quantity)
  }));
}

function formatServiceOptionLabel(service) {
  const serviceName = service.name ?? '';
  const measurementUnit = service.measurement_unit ?? '';

  return measurementUnit ? `${serviceName} (${measurementUnit})` : serviceName;
}

function hasValue(value) {
  return value !== null && value !== undefined && value !== '';
}
</script>

<template>
  <q-dialog v-model="dialogOpen" :persistent="isSavingRouteStop">
    <q-card class="dispatcher-route-stop-dialog" bordered flat>
      <q-form
        ref="formRef"
        greedy
        :aria-label="ariaLabel"
        @submit.prevent="handleSubmit"
      >
        <q-card-section class="row no-wrap items-start q-pa-lg q-pb-sm">
          <q-avatar
            square
            size="42px"
            class="dispatcher-route-stop-dialog-icon q-mr-md"
            aria-hidden="true"
          >
            <q-icon name="pin_drop" size="24px" />
          </q-avatar>

          <div class="col">
            <h2 class="text-h6 text-weight-bold q-my-none">
              {{ dialogTitle }}
            </h2>
          </div>

          <q-btn
            flat
            round
            dense
            icon="close"
            :aria-label="t('dispatcherRouteEdit.routeStops.form.actions.close')"
            :disable="isSavingRouteStop"
            @click="dialogOpen = false"
          >
            <q-tooltip>
              {{ t('dispatcherRouteEdit.routeStops.form.actions.close') }}
            </q-tooltip>
          </q-btn>
        </q-card-section>

        <q-card-section
          class="dispatcher-route-stop-dialog-body q-px-lg q-py-md"
        >
          <div class="dispatcher-route-stop-dialog-grid">
            <TextField
              v-model="form.location"
              class="dispatcher-route-stop-location-field"
              :label="locationLabel"
              name="location"
              :placeholder="
                t(
                  'dispatcherRouteEdit.routeStops.form.fields.location.placeholder'
                )
              "
              :rules="activeLocationRules"
              :maxlength="255"
              :disable="topFieldsDisabled"
            />

            <TextField
              v-model="form.numberOfTrucks"
              type="number"
              min="1"
              step="1"
              :label="numberOfTrucksLabel"
              name="number_of_trucks"
              :placeholder="
                t(
                  'dispatcherRouteEdit.routeStops.form.fields.numberOfTrucks.placeholder'
                )
              "
              :rules="activeNumberOfTrucksRules"
              :disable="topFieldsDisabled"
            />

            <TextField
              v-model="form.numberOfDrivers"
              type="number"
              min="1"
              step="1"
              :label="numberOfDriversLabel"
              name="number_of_drivers"
              :placeholder="
                t(
                  'dispatcherRouteEdit.routeStops.form.fields.numberOfDrivers.placeholder'
                )
              "
              :rules="activeNumberOfDriversRules"
              :disable="topFieldsDisabled"
            />
          </div>

          <TextField
            v-model="form.description"
            type="textarea"
            autogrow
            rows="5"
            :input-style="{ minHeight: '120px' }"
            :label="descriptionLabel"
            name="description"
            :placeholder="
              t(
                'dispatcherRouteEdit.routeStops.form.fields.description.placeholder'
              )
            "
            :disable="topFieldsDisabled"
          />

          <section
            class="dispatcher-route-stop-services"
            :aria-label="t('dispatcherRouteEdit.routeStops.form.servicesTitle')"
          >
            <h3 class="text-subtitle2 text-weight-bold q-my-none">
              {{ t('dispatcherRouteEdit.routeStops.form.servicesTitle') }}
            </h3>

            <div
              v-for="serviceRow in form.services"
              :key="serviceRow.uid"
              class="dispatcher-route-stop-service-row"
            >
              <q-select
                v-model="serviceRow.serviceId"
                class="dispatcher-route-stop-service-select"
                outlined
                emit-value
                map-options
                clearable
                option-label="label"
                option-value="value"
                :options="serviceOptionsFor(serviceRow)"
                :label="serviceLabel"
                :placeholder="
                  t(
                    'dispatcherRouteEdit.routeStops.form.fields.service.placeholder'
                  )
                "
                :loading="isFetchingServices"
                :disable="serviceFieldsDisabled || !hasServiceOptions"
                :rules="serviceRules(serviceRow)"
                no-error-icon
                @update:model-value="
                  serviceId => handleServiceChange(serviceRow, serviceId)
                "
              />

              <q-input
                v-model="serviceRow.quantity"
                class="dispatcher-route-stop-quantity-field"
                outlined
                lazy-rules
                type="number"
                min="1"
                step="1"
                :label="quantityLabel"
                :placeholder="
                  t(
                    'dispatcherRouteEdit.routeStops.form.fields.quantity.placeholder'
                  )
                "
                :disable="
                  serviceFieldsDisabled || !hasValue(serviceRow.serviceId)
                "
                :rules="quantityRules"
                no-error-icon
              >
                <template v-if="hasValue(serviceRow.serviceId)" #append>
                  <span class="dispatcher-route-stop-quantity-unit">
                    {{ selectedMeasurementUnit(serviceRow) }}
                  </span>
                </template>
              </q-input>

              <q-btn
                class="dispatcher-route-stop-service-remove"
                flat
                round
                color="negative"
                icon="delete_outline"
                :aria-label="
                  t('dispatcherRouteEdit.routeStops.form.actions.removeService')
                "
                :disable="isSavingRouteStop"
                @click="removeServiceRow(serviceRow.uid)"
              >
                <q-tooltip>
                  {{
                    t(
                      'dispatcherRouteEdit.routeStops.form.actions.removeService'
                    )
                  }}
                </q-tooltip>
              </q-btn>
            </div>

            <q-banner
              v-if="!isFetchingServices && !hasServiceOptions"
              dense
              rounded
              class="bg-grey-2 text-grey-8"
            >
              {{ t('dispatcherRouteEdit.routeStops.form.noServices') }}
            </q-banner>

            <q-btn
              class="dispatcher-route-stop-service-add"
              type="button"
              color="primary"
              icon="add"
              round
              outline
              :aria-label="
                t('dispatcherRouteEdit.routeStops.form.actions.addService')
              "
              :disable="!canAddServiceRow"
              @click="addServiceRow"
            >
              <q-tooltip>
                {{
                  t('dispatcherRouteEdit.routeStops.form.actions.addService')
                }}
              </q-tooltip>
            </q-btn>
          </section>
        </q-card-section>

        <q-card-actions class="q-pa-lg q-gutter-sm" align="right">
          <q-btn
            class="text-weight-bold"
            type="button"
            flat
            no-caps
            :label="t('dispatcherRouteEdit.routeStops.form.actions.close')"
            :disable="isSavingRouteStop"
            v-close-popup
          />

          <q-btn
            class="text-weight-bold"
            type="submit"
            color="primary"
            icon="save"
            no-caps
            unelevated
            :label="t('dispatcherRouteEdit.routeStops.form.actions.save')"
            :loading="isSavingRouteStop"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>
</template>
