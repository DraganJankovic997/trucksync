<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TextField from '@/components/form/TextField.vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits({
  'update:modelValue': value => typeof value === 'string',
  create: value => typeof value === 'string'
});

const { t } = useI18n();
const formRef = ref(null);

const serviceName = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const required = value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: t('services.form.name.label') });

const maxLength = length => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', {
    field: t('services.form.name.label'),
    length: length
  });

const nameRules = [required, maxLength(255)];

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  emit('create', serviceName.value.trim());
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
          :label="t('services.form.name.label')"
          name="service_name"
          :placeholder="t('services.form.name.placeholder')"
          :rules="nameRules"
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
