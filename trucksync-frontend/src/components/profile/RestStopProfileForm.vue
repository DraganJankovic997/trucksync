<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CountrySelectField from '@/components/form/CountrySelectField.vue';
import TextField from '@/components/form/TextField.vue';
import { useRestStopStore } from '@/stores/rest-stop.js';

const { t } = useI18n();
const restStopStore = useRestStopStore();
const formRef = ref(null);
const isSaving = ref(false);

const form = reactive({
  country: '',
  city: '',
  address: '',
  postCode: '',
  worksFrom: '',
  worksTo: ''
});

const required = fieldKey => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: t(fieldKey) });

const maxLength = (fieldKey, length) => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', { field: t(fieldKey), length: length });

const timeFormat = fieldKey => value =>
  /^([01]\d|2[0-3]):[0-5]\d$/.test(String(value ?? '').trim()) ||
  t('validation.time', { field: t(fieldKey) });

const fieldRules = fieldKey => [required(fieldKey), maxLength(fieldKey, 255)];
const timeRules = fieldKey => [required(fieldKey), timeFormat(fieldKey)];

onMounted(async () => {
  const restStop = await restStopStore.fetchRestStop();

  if (restStop) {
    form.country = restStop.country ?? '';
    form.city = restStop.city ?? '';
    form.address = restStop.address ?? '';
    form.postCode = restStop.post_code ?? '';
    form.worksFrom = restStop.works_from ?? '';
    form.worksTo = restStop.works_to ?? '';
  }
});

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  isSaving.value = true;

  try {
    await restStopStore.saveRestStop(
      form.country,
      form.city,
      form.address,
      form.postCode,
      form.worksFrom,
      form.worksTo
    );
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
    :aria-label="t('profile.typeForms.restStop.formAriaLabel')"
    @submit.prevent="handleSubmit"
  >
    <q-card class="profile-form" bordered flat>
      <q-card-section class="form-header">
        <h2>{{ t('profile.typeForms.restStop.title') }}</h2>
      </q-card-section>

      <q-card-section class="form-body">
        <div class="profile-grid">
          <CountrySelectField
            v-model="form.country"
            :label="t('profile.typeForms.restStop.fields.country.label')"
            name="country"
            :placeholder="
              t('profile.typeForms.restStop.fields.country.placeholder')
            "
            :rules="fieldRules('validation.fields.country')"
          />

          <TextField
            v-model="form.city"
            :label="t('profile.typeForms.restStop.fields.city.label')"
            name="city"
            :placeholder="
              t('profile.typeForms.restStop.fields.city.placeholder')
            "
            :rules="fieldRules('validation.fields.city')"
            :maxlength="255"
          />
        </div>

        <TextField
          v-model="form.address"
          :label="t('profile.typeForms.restStop.fields.address.label')"
          name="address"
          :placeholder="
            t('profile.typeForms.restStop.fields.address.placeholder')
          "
          :rules="fieldRules('validation.fields.address')"
          :maxlength="255"
        />

        <TextField
          v-model="form.postCode"
          :label="t('profile.typeForms.restStop.fields.postCode.label')"
          name="post_code"
          :placeholder="
            t('profile.typeForms.restStop.fields.postCode.placeholder')
          "
          :rules="fieldRules('validation.fields.postCode')"
          :maxlength="255"
        />

        <div class="profile-grid">
          <q-input
            v-model="form.worksFrom"
            class="form-field"
            outlined
            lazy-rules
            type="time"
            name="works_from"
            :label="t('profile.typeForms.restStop.fields.worksFrom.label')"
            :rules="timeRules('validation.fields.worksFrom')"
          />

          <q-input
            v-model="form.worksTo"
            class="form-field"
            outlined
            lazy-rules
            type="time"
            name="works_to"
            :label="t('profile.typeForms.restStop.fields.worksTo.label')"
            :rules="timeRules('validation.fields.worksTo')"
          />
        </div>
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
