<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TextField from '@/components/form/TextField.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      name: '',
      measurementUnit: ''
    })
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits({
  'update:modelValue': value =>
    typeof value?.name === 'string' &&
    typeof value?.measurementUnit === 'string',
  create: value =>
    typeof value?.name === 'string' &&
    (typeof value?.measurementUnit === 'string' ||
      value?.measurementUnit === null)
});

const { t } = useI18n();
const formRef = ref(null);

const serviceName = computed({
  get() {
    return props.modelValue.name;
  },
  set(value) {
    emit('update:modelValue', {
      ...props.modelValue,
      name: value
    });
  }
});

const measurementUnit = computed({
  get() {
    return props.modelValue.measurementUnit;
  },
  set(value) {
    emit('update:modelValue', {
      ...props.modelValue,
      measurementUnit: value
    });
  }
});

const required = fieldLabel => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: fieldLabel });

const maxLength = (fieldLabel, length) => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', {
    field: fieldLabel,
    length: length
  });

const nameLabel = computed(() => t('services.form.name.label'));
const measurementUnitLabel = computed(() =>
  t('services.form.measurementUnit.label')
);
const nameRules = computed(() => [
  required(nameLabel.value),
  maxLength(nameLabel.value, 255)
]);
const measurementUnitRules = computed(() => [
  maxLength(measurementUnitLabel.value, 255)
]);

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  emit('create', {
    name: serviceName.value.trim(),
    measurementUnit: measurementUnit.value.trim() || null
  });
}
</script>

<template>
  <q-form
    ref="formRef"
    greedy
    :aria-label="t('services.form.ariaLabel')"
    @submit.prevent="handleSubmit"
  >
    <q-card class="service-card" bordered flat>
      <q-card-section class="q-pa-lg q-pb-none">
        <h2 class="text-h6 text-weight-bold q-my-none">
          {{ t('services.form.title') }}
        </h2>
      </q-card-section>

      <q-card-section class="q-px-lg q-py-md">
        <TextField
          v-model="serviceName"
          :label="nameLabel"
          name="service_name"
          :placeholder="t('services.form.name.placeholder')"
          :rules="nameRules"
          :maxlength="255"
        />

        <TextField
          v-model="measurementUnit"
          :label="measurementUnitLabel"
          name="measurement_unit"
          :placeholder="t('services.form.measurementUnit.placeholder')"
          :rules="measurementUnitRules"
          :maxlength="255"
        />
      </q-card-section>

      <q-card-actions class="q-px-lg q-pb-lg q-pt-none">
        <q-btn
          class="full-width text-weight-bold"
          type="submit"
          color="primary"
          icon="add"
          :label="t('services.form.submit')"
          :loading="props.loading"
          no-caps
          unelevated
        />
      </q-card-actions>
    </q-card>
  </q-form>
</template>
