<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import TextField from '@/components/form/TextField.vue';
import { useRouteStore } from '@/stores/route.js';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits({
  'update:modelValue': value => typeof value === 'boolean'
});

const { t } = useI18n();
const routeStore = useRouteStore();
const formRef = ref(null);
const isSaving = ref(false);

const form = reactive({
  origin: '',
  destination: '',
  plannedTravelDetails: '',
  convoySize: '',
  startDate: '',
  endDate: ''
});

const dialogOpen = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const minimumRouteDate = computed(() =>
  getDateInputValue(addDays(new Date(), 1))
);

const minimumEndDate = computed(() => {
  if (
    form.startDate &&
    isValidDateValue(form.startDate) &&
    form.startDate > minimumRouteDate.value
  ) {
    return form.startDate;
  }

  return minimumRouteDate.value;
});

const required = fieldKey => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: t(fieldKey) });

const integer = fieldKey => value =>
  /^\d+$/.test(String(value ?? '').trim()) ||
  t('validation.integer', { field: t(fieldKey) });

const min = (fieldKey, minimum) => value =>
  Number(value) >= minimum ||
  t('validation.min', { field: t(fieldKey), min: minimum });

const date = fieldKey => value =>
  !value ||
  isValidDateValue(value) ||
  t('validation.date', { field: t(fieldKey) });

const futureDate = fieldKey => value =>
  !value ||
  !isValidDateValue(value) ||
  parseDateInputValue(value) > getToday() ||
  t('validation.futureDate', { field: t(fieldKey) });

const dateAfterOrEqual =
  (fieldKey, comparisonFieldKey, comparisonValue) => value =>
    !value ||
    !comparisonValue ||
    !isValidDateValue(value) ||
    !isValidDateValue(comparisonValue) ||
    parseDateInputValue(value) >= parseDateInputValue(comparisonValue) ||
    t('validation.afterOrEqual', {
      field: t(fieldKey),
      comparison: t(comparisonFieldKey)
    });

const originRules = [required('validation.fields.origin')];
const destinationRules = [required('validation.fields.destination')];
const convoySizeRules = [
  required('validation.fields.convoySize'),
  integer('validation.fields.convoySize'),
  min('validation.fields.convoySize', 1)
];
const startDateRules = [
  required('validation.fields.startDate'),
  date('validation.fields.startDate'),
  futureDate('validation.fields.startDate')
];
const endDateRules = [
  required('validation.fields.endDate'),
  date('validation.fields.endDate'),
  futureDate('validation.fields.endDate'),
  value =>
    dateAfterOrEqual(
      'validation.fields.endDate',
      'validation.fields.startDate',
      form.startDate
    )(value)
];

function addDays(dateValue, days) {
  const nextDate = new Date(dateValue);
  nextDate.setDate(nextDate.getDate() + days);

  return nextDate;
}

function getDateInputValue(dateValue) {
  const year = dateValue.getFullYear();
  const month = String(dateValue.getMonth() + 1).padStart(2, '0');
  const day = String(dateValue.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function getToday() {
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  return today;
}

function isValidDateValue(value) {
  const dateMatch = String(value ?? '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

  if (!dateMatch) {
    return false;
  }

  const [, year, month, day] = dateMatch;
  const parsedDate = parseDateInputValue(value);

  return (
    parsedDate.getFullYear() === Number(year) &&
    parsedDate.getMonth() + 1 === Number(month) &&
    parsedDate.getDate() === Number(day)
  );
}

function parseDateInputValue(value) {
  return new Date(`${value}T00:00:00`);
}

watch(dialogOpen, isOpen => {
  if (!isOpen) {
    resetForm();
  }
});

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  isSaving.value = true;

  try {
    const createdRoute = await routeStore.createRoute(
      form.origin.trim(),
      form.destination.trim(),
      Number(form.convoySize),
      form.startDate,
      form.endDate,
      form.plannedTravelDetails.trim() || null
    );

    if (createdRoute) {
      dialogOpen.value = false;
    }
  } finally {
    isSaving.value = false;
  }
}

function resetForm() {
  form.origin = '';
  form.destination = '';
  form.plannedTravelDetails = '';
  form.convoySize = '';
  form.startDate = '';
  form.endDate = '';
  formRef.value?.resetValidation();
}
</script>

<template>
  <q-dialog v-model="dialogOpen" :persistent="isSaving">
    <q-card class="dispatcher-route-create-dialog" bordered flat>
      <q-form
        ref="formRef"
        greedy
        :aria-label="t('dispatcherRoutes.form.ariaLabel')"
        @submit.prevent="handleSubmit"
      >
        <q-card-section class="row no-wrap q-pa-lg q-pb-sm">
          <q-avatar
            square
            size="42px"
            class="dispatcher-route-create-icon q-mr-md"
            aria-hidden="true"
          >
            <q-icon name="add_road" size="24px" />
          </q-avatar>

          <div>
            <h2 class="text-h6 text-weight-bold q-my-none">
              {{ t('dispatcherRoutes.form.title') }}
            </h2>
          </div>
        </q-card-section>

        <q-card-section class="dispatcher-route-create-body q-px-lg q-py-md">
          <div class="dispatcher-route-create-grid">
            <TextField
              v-model="form.origin"
              :label="t('dispatcherRoutes.form.fields.origin.label')"
              name="origin"
              :placeholder="
                t('dispatcherRoutes.form.fields.origin.placeholder')
              "
              :rules="originRules"
            />

            <TextField
              v-model="form.destination"
              :label="t('dispatcherRoutes.form.fields.destination.label')"
              name="destination"
              :placeholder="
                t('dispatcherRoutes.form.fields.destination.placeholder')
              "
              :rules="destinationRules"
            />

            <TextField
              v-model="form.convoySize"
              type="number"
              min="1"
              step="1"
              :label="t('dispatcherRoutes.form.fields.convoySize.label')"
              name="convoy_size"
              :placeholder="
                t('dispatcherRoutes.form.fields.convoySize.placeholder')
              "
              :rules="convoySizeRules"
            />

            <TextField
              v-model="form.startDate"
              type="date"
              stack-label
              :min="minimumRouteDate"
              :label="t('dispatcherRoutes.form.fields.startDate.label')"
              name="start_date"
              :rules="startDateRules"
            />

            <TextField
              v-model="form.endDate"
              type="date"
              stack-label
              :min="minimumEndDate"
              :label="t('dispatcherRoutes.form.fields.endDate.label')"
              name="end_date"
              :rules="endDateRules"
            />
          </div>

          <TextField
            v-model="form.plannedTravelDetails"
            type="textarea"
            autogrow
            rows="5"
            :input-style="{ minHeight: '120px' }"
            :label="
              t('dispatcherRoutes.form.fields.plannedTravelDetails.label')
            "
            name="planned_travel_details"
            :placeholder="
              t('dispatcherRoutes.form.fields.plannedTravelDetails.placeholder')
            "
          />
        </q-card-section>

        <q-card-actions class="q-pa-lg q-gutter-sm" align="right">
          <q-btn
            class="text-weight-bold"
            type="button"
            flat
            no-caps
            :label="t('dispatcherRoutes.form.actions.cancel')"
            :disable="isSaving"
            v-close-popup
          />

          <q-btn
            class="text-weight-bold"
            type="submit"
            color="primary"
            icon="add"
            no-caps
            unelevated
            :label="t('dispatcherRoutes.form.actions.create')"
            :loading="isSaving"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>
</template>
