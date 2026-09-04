<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CountrySelectField from '@/components/form/CountrySelectField.vue';
import TextField from '@/components/form/TextField.vue';
import { useDispatcherStore } from '@/stores/dispatcher.js';

const { t } = useI18n();
const dispatcherStore = useDispatcherStore();
const formRef = ref(null);
const isSaving = ref(false);

const form = reactive({
  companyName: '',
  country: '',
  city: '',
  address: '',
  postCode: '',
  registrationNumber: ''
});

const required = fieldKey => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: t(fieldKey) });

const maxLength = (fieldKey, length) => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', { field: t(fieldKey), length: length });

const fieldRules = fieldKey => [required(fieldKey), maxLength(fieldKey, 255)];

onMounted(async () => {
  const dispatcher = await dispatcherStore.fetchDispatcher();

  if (dispatcher) {
    form.companyName = dispatcher.company_name ?? '';
    form.country = dispatcher.country ?? '';
    form.city = dispatcher.city ?? '';
    form.address = dispatcher.address ?? '';
    form.postCode = dispatcher.post_code ?? '';
    form.registrationNumber = dispatcher.registration_number ?? '';
  }
});

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  isSaving.value = true;

  try {
    await dispatcherStore.saveDispatcher(
      form.companyName,
      form.country,
      form.city,
      form.address,
      form.postCode,
      form.registrationNumber
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
    :aria-label="t('profile.typeForms.dispatcher.formAriaLabel')"
    @submit.prevent="handleSubmit"
  >
    <q-card class="profile-form" bordered flat>
      <q-card-section class="form-header">
        <h2>{{ t('profile.typeForms.dispatcher.title') }}</h2>
      </q-card-section>

      <q-card-section class="form-body">
        <TextField
          v-model="form.companyName"
          :label="t('profile.typeForms.dispatcher.fields.companyName.label')"
          name="company_name"
          :placeholder="
            t('profile.typeForms.dispatcher.fields.companyName.placeholder')
          "
          :rules="fieldRules('validation.fields.companyName')"
          :maxlength="255"
        />

        <div class="profile-grid">
          <CountrySelectField
            v-model="form.country"
            :label="t('profile.typeForms.dispatcher.fields.country.label')"
            name="country"
            :placeholder="
              t('profile.typeForms.dispatcher.fields.country.placeholder')
            "
            :rules="fieldRules('validation.fields.country')"
          />

          <TextField
            v-model="form.city"
            :label="t('profile.typeForms.dispatcher.fields.city.label')"
            name="city"
            :placeholder="
              t('profile.typeForms.dispatcher.fields.city.placeholder')
            "
            :rules="fieldRules('validation.fields.city')"
            :maxlength="255"
          />
        </div>

        <TextField
          v-model="form.address"
          :label="t('profile.typeForms.dispatcher.fields.address.label')"
          name="address"
          :placeholder="
            t('profile.typeForms.dispatcher.fields.address.placeholder')
          "
          :rules="fieldRules('validation.fields.address')"
          :maxlength="255"
        />

        <div class="profile-grid">
          <TextField
            v-model="form.postCode"
            :label="t('profile.typeForms.dispatcher.fields.postCode.label')"
            name="post_code"
            :placeholder="
              t('profile.typeForms.dispatcher.fields.postCode.placeholder')
            "
            :rules="fieldRules('validation.fields.postCode')"
            :maxlength="255"
          />

          <TextField
            v-model="form.registrationNumber"
            :label="
              t('profile.typeForms.dispatcher.fields.registrationNumber.label')
            "
            name="registration_number"
            :placeholder="
              t(
                'profile.typeForms.dispatcher.fields.registrationNumber.placeholder'
              )
            "
            :rules="fieldRules('validation.fields.registrationNumber')"
            :maxlength="255"
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
