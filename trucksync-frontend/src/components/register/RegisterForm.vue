<script setup>
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import EmailField from '@/components/form/EmailField.vue';
import PasswordField from '@/components/form/PasswordField.vue';
import TextField from '@/components/form/TextField.vue';
import { useAuthStore } from '@/stores/auth.js';

const authStore = useAuthStore();
const formRef = ref(null);
const { t } = useI18n();

const form = reactive({
  firstName: '',
  lastName: '',
  email: '',
  profileType: '',
  password: '',
  passwordConfirmation: ''
});

const profileTypeValues = ['driver', 'dispatcher', 'rest_stop'];

const required = fieldKey => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: t(fieldKey) });

const maxLength = (fieldKey, length) => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', { field: t(fieldKey), length: length });

const nameRules = fieldKey => [required(fieldKey), maxLength(fieldKey, 255)];

const emailRules = [
  required('validation.fields.email'),
  value =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value ?? '')) ||
    t('validation.email'),
  maxLength('validation.fields.email', 255)
];

const passwordRules = [
  required('validation.fields.password'),
  value =>
    String(value ?? '').length >= 8 ||
    t('validation.minLength', {
      field: t('validation.fields.password'),
      length: 8
    })
];

const confirmPasswordRules = [
  required('validation.fields.passwordConfirmation'),
  value => value === form.password || t('validation.confirmed')
];

const profileTypeOptions = computed(() => [
  {
    label: t('profile.profileTypes.driver'),
    value: 'driver'
  },
  {
    label: t('profile.profileTypes.dispatcher'),
    value: 'dispatcher'
  },
  {
    label: t('profile.profileTypes.restStop'),
    value: 'rest_stop'
  }
]);

const profileTypeRules = [
  required('validation.fields.profileType'),
  value =>
    profileTypeValues.includes(value) ||
    t('validation.invalidChoice', {
      field: t('validation.fields.profileType')
    })
];

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  await authStore.register({
    first_name: form.firstName,
    last_name: form.lastName,
    email: form.email,
    profile_type: form.profileType,
    password: form.password,
    password_confirmation: form.passwordConfirmation
  });
}
</script>

<template>
  <q-form ref="formRef" greedy @submit.prevent="handleSubmit">
    <q-card class="register-form" bordered flat>
      <q-card-section class="form-header">
        <h2>{{ t('register.form.title') }}</h2>
      </q-card-section>

      <q-card-section class="form-body">
        <div class="name-grid">
          <TextField
            v-model="form.firstName"
            :label="t('register.fields.firstName.label')"
            name="first_name"
            :placeholder="t('register.fields.firstName.placeholder')"
            :rules="nameRules('validation.fields.firstName')"
            :maxlength="255"
          />

          <TextField
            v-model="form.lastName"
            :label="t('register.fields.lastName.label')"
            name="last_name"
            :placeholder="t('register.fields.lastName.placeholder')"
            :rules="nameRules('validation.fields.lastName')"
            :maxlength="255"
          />
        </div>

        <EmailField
          v-model="form.email"
          :label="t('register.fields.email.label')"
          :placeholder="t('register.fields.email.placeholder')"
          :rules="emailRules"
          :maxlength="255"
        />

        <q-select
          v-model="form.profileType"
          class="form-field"
          outlined
          lazy-rules
          emit-value
          map-options
          name="profile_type"
          :label="t('register.fields.profileType.label')"
          :options="profileTypeOptions"
          :rules="profileTypeRules"
        />

        <PasswordField
          v-model="form.password"
          :label="t('register.fields.password.label')"
          name="password"
          :placeholder="t('register.fields.password.placeholder')"
          :rules="passwordRules"
        />

        <PasswordField
          v-model="form.passwordConfirmation"
          :label="t('register.fields.passwordConfirmation.label')"
          name="password_confirmation"
          :placeholder="t('register.fields.passwordConfirmation.placeholder')"
          :rules="confirmPasswordRules"
        />
      </q-card-section>

      <q-card-actions class="form-actions">
        <q-btn
          class="register-form-submit"
          type="submit"
          color="primary"
          icon="person_add"
          :label="t('register.submit')"
          no-caps
          unelevated
        />
      </q-card-actions>
    </q-card>
  </q-form>
</template>
