<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DispatcherSelectField from '@/components/form/DispatcherSelectField.vue';
import TextField from '@/components/form/TextField.vue';
import { useDriverStore } from '@/stores/driver.js';

const { t } = useI18n();
const driverStore = useDriverStore();
const formRef = ref(null);
const isSaving = ref(false);

const form = reactive({
  licenseNumber: '',
  dispatcherId: null
});

const required = fieldKey => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: t(fieldKey) });

const maxLength = (fieldKey, length) => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', { field: t(fieldKey), length: length });

const licenseNumberRules = [
  required('validation.fields.licenseNumber'),
  maxLength('validation.fields.licenseNumber', 255)
];

onMounted(async () => {
  const driver = await driverStore.fetchDriver();

  if (driver) {
    form.licenseNumber = driver.license_number ?? '';
    form.dispatcherId = driver.dispatcher_id ?? null;
  }
});

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  isSaving.value = true;

  try {
    await driverStore.saveDriver(form.licenseNumber, form.dispatcherId);
  } finally {
    isSaving.value = false;
  }
}
</script>

<template>
  <q-form
    ref="formRef"
    greedy
    class="profile-form-wrapper"
    :aria-label="t('profile.typeForms.driver.formAriaLabel')"
    @submit.prevent="handleSubmit"
  >
    <q-card class="profile-form" bordered flat>
      <q-card-section class="form-header">
        <h2>{{ t('profile.typeForms.driver.title') }}</h2>
      </q-card-section>

      <q-card-section class="form-body">
        <TextField
          v-model="form.licenseNumber"
          :label="t('profile.typeForms.driver.fields.licenseNumber.label')"
          name="license_number"
          :placeholder="
            t('profile.typeForms.driver.fields.licenseNumber.placeholder')
          "
          :rules="licenseNumberRules"
          :maxlength="255"
        />

        <DispatcherSelectField
          v-model="form.dispatcherId"
          :label="t('profile.typeForms.driver.fields.dispatcherId.label')"
          :placeholder="
            t('profile.typeForms.driver.fields.dispatcherId.placeholder')
          "
        />
      </q-card-section>

      <q-card-actions class="form-actions">
        <q-btn
          class="profile-form-submit"
          type="submit"
          color="primary"
          icon="save"
          :label="t('profile.submit')"
          :loading="isSaving"
          no-caps
          unelevated
        />
      </q-card-actions>
    </q-card>
  </q-form>
</template>
