<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import TextField from '@/components/form/TextField.vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  mode: {
    type: String,
    default: 'create',
    validator: value => ['create', 'edit'].includes(value)
  },
  routeStop: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits({
  'update:modelValue': value => typeof value === 'boolean',
  save: value =>
    ['create', 'edit'].includes(value?.mode) &&
    typeof value?.location === 'string' &&
    (typeof value?.description === 'string' || value?.description === null) &&
    Number.isInteger(value?.numberOfTrucks) &&
    Number.isInteger(value?.numberOfDrivers)
});

const { t } = useI18n();
const formRef = ref(null);

const form = reactive({
  location: '',
  description: '',
  numberOfTrucks: '',
  numberOfDrivers: ''
});

const dialogOpen = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const isEditMode = computed(() => props.mode === 'edit');
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

const required = fieldLabel => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: fieldLabel });

const integer = fieldLabel => value =>
  /^\d+$/.test(String(value ?? '').trim()) ||
  t('validation.integer', { field: fieldLabel });

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
const numberOfTrucksRules = computed(() => [
  required(numberOfTrucksLabel.value),
  integer(numberOfTrucksLabel.value),
  min(numberOfTrucksLabel.value, 1)
]);
const numberOfDriversRules = computed(() => [
  required(numberOfDriversLabel.value),
  integer(numberOfDriversLabel.value),
  min(numberOfDriversLabel.value, 1)
]);

watch(dialogOpen, isOpen => {
  if (isOpen) {
    hydrateForm();
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

  emit('save', {
    id: props.routeStop?.id ?? null,
    mode: props.mode,
    location: form.location.trim(),
    description: form.description.trim() || null,
    numberOfTrucks: Number(form.numberOfTrucks),
    numberOfDrivers: Number(form.numberOfDrivers)
  });
}

function hydrateForm() {
  form.location = props.routeStop?.location ?? '';
  form.description = props.routeStop?.description ?? '';
  form.numberOfTrucks = props.routeStop?.number_of_trucks ?? '';
  form.numberOfDrivers = props.routeStop?.number_of_drivers ?? '';
  formRef.value?.resetValidation();
}

function resetForm() {
  form.location = '';
  form.description = '';
  form.numberOfTrucks = '';
  form.numberOfDrivers = '';
  formRef.value?.resetValidation();
}
</script>

<template>
  <q-dialog v-model="dialogOpen" :persistent="props.loading">
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
            :disable="props.loading"
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
              :label="locationLabel"
              name="location"
              :placeholder="
                t(
                  'dispatcherRouteEdit.routeStops.form.fields.location.placeholder'
                )
              "
              :rules="locationRules"
              :maxlength="255"
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
              :rules="numberOfTrucksRules"
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
              :rules="numberOfDriversRules"
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
          />
        </q-card-section>

        <q-card-actions class="q-pa-lg q-gutter-sm" align="right">
          <q-btn
            class="text-weight-bold"
            type="button"
            flat
            no-caps
            :label="t('dispatcherRouteEdit.routeStops.form.actions.close')"
            :disable="props.loading"
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
            :loading="props.loading"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>
</template>
